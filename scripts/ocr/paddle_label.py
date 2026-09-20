#!/usr/bin/env python3
"""
Lê uma imagem de etiqueta e devolve JSON: {"text": "...", "confidence": 0.0-1.0}
Uso: python paddle_label.py /caminho/imagem.jpg
Compatível com PaddleOCR 2.x e 3.x (PaddleX pipeline).
"""
import json
import os
import sys


def configure_runtime() -> None:
    """Evita crash oneDNN/PIR no PaddlePaddle 3.3+ em CPU (Windows/Linux)."""
    os.environ.setdefault("PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK", "True")
    os.environ.setdefault("FLAGS_enable_pir_api", "0")
    os.environ.setdefault("FLAGS_use_mkldnn", "0")
    os.environ.setdefault("PADDLE_PDX_ENABLE_MKLDNN_BYDEFAULT", "0")

    runtime_home = os.environ.get("PADDLE_OCR_HOME")
    if runtime_home:
        os.makedirs(runtime_home, exist_ok=True)
        os.environ["HOME"] = runtime_home
        os.environ["USERPROFILE"] = runtime_home
        os.environ["XDG_CACHE_HOME"] = os.path.join(runtime_home, "cache")
        os.environ["PADDLE_HOME"] = os.path.join(runtime_home, ".paddle")
        os.environ["PADDLEX_HOME"] = os.path.join(runtime_home, ".paddlex")
        os.makedirs(os.environ["XDG_CACHE_HOME"], exist_ok=True)
        os.makedirs(os.environ["PADDLE_HOME"], exist_ok=True)
        os.makedirs(os.environ["PADDLEX_HOME"], exist_ok=True)


def emit_error(code: str, message: str) -> None:
    print(json.dumps({"error": code, "message": message, "text": "", "confidence": 0}), file=sys.stderr)
    sys.exit(3)


def parse_ocr_result(result) -> tuple[str, list[float]]:
    lines: list[str] = []
    confidences: list[float] = []

    if not result:
        return "", confidences

    # PaddleOCR 3.x predict(): list[dict] com rec_texts / rec_scores
    if isinstance(result, list) and result and isinstance(result[0], dict):
        for block in result:
            if not isinstance(block, dict):
                continue
            texts = block.get("rec_texts") or block.get("texts") or []
            scores = block.get("rec_scores") or block.get("scores") or []
            for idx, text in enumerate(texts):
                if text:
                    lines.append(str(text))
                if idx < len(scores):
                    try:
                        confidences.append(float(scores[idx]))
                    except (TypeError, ValueError):
                        pass

        if lines:
            return "\n".join(lines), confidences

    # PaddleOCR 2.x: list[list[ [box], (text, conf) ]]
    if isinstance(result, list):
        for block in result:
            if not block or isinstance(block, dict):
                continue
            for item in block:
                if not item or len(item) < 2:
                    continue
                text_part = item[1]
                if not text_part or len(text_part) < 2:
                    continue
                lines.append(str(text_part[0]))
                try:
                    confidences.append(float(text_part[1]))
                except (TypeError, ValueError):
                    pass

    return "\n".join(lines), confidences


def create_ocr():
    configure_runtime()
    from paddleocr import PaddleOCR

    try:
        return PaddleOCR(lang="pt", enable_mkldnn=False)
    except TypeError:
        try:
            return PaddleOCR(lang="pt")
        except (TypeError, ValueError):
            return PaddleOCR(use_angle_cls=True, lang="pt", show_log=False)


def run_ocr(ocr, image_path: str):
    if hasattr(ocr, "predict"):
        return ocr.predict(image_path)
    if hasattr(ocr, "ocr"):
        try:
            return ocr.ocr(image_path, cls=True)
        except TypeError:
            return ocr.ocr(image_path)
    raise RuntimeError("paddle_api_unsupported")


def main() -> None:
    configure_runtime()

    if len(sys.argv) < 2:
        emit_error("missing_image_path", "Caminho da imagem não informado.")

    image_path = sys.argv[1]
    if not os.path.isfile(image_path):
        emit_error("image_not_found", f"Arquivo não encontrado: {image_path}")

    try:
        from paddleocr import PaddleOCR  # noqa: F401
    except ImportError:
        print(json.dumps({"error": "paddleocr_not_installed", "text": "", "confidence": 0}))
        sys.exit(2)

    try:
        ocr = create_ocr()
        result = run_ocr(ocr, image_path)
        text, confidences = parse_ocr_result(result)
        confidence = 0.0
        if confidences:
            confidence = round(sum(confidences) / len(confidences), 4)

        print(json.dumps({"text": text, "confidence": confidence}, ensure_ascii=False))
    except Exception as exc:  # noqa: BLE001
        emit_error("paddle_runtime_error", str(exc))


if __name__ == "__main__":
    main()
