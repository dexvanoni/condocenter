<style>
.announce-page { --announce-accent: #d97706; }
.announce-compose {
	background: #fff;
	border: 1px solid #e8ecf1;
	border-radius: 16px;
	overflow: hidden;
	box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}
.announce-compose__section {
	padding: 1.25rem 1.5rem;
	border-bottom: 1px solid #f1f5f9;
}
.announce-compose__section:last-child { border-bottom: 0; }
.announce-compose__section-title {
	font-size: 0.7rem;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	font-weight: 700;
	color: #94a3b8;
	margin-bottom: 0.75rem;
}
.announce-prio-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 8px;
}
@media (min-width: 576px) {
	.announce-prio-grid { grid-template-columns: repeat(4, 1fr); }
}
.announce-prio-option {
	position: relative;
	margin: 0;
}
.announce-prio-option input {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}
.announce-prio-option label {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 4px;
	padding: 12px 8px;
	border: 2px solid #e2e8f0;
	border-radius: 12px;
	background: #f8fafc;
	cursor: pointer;
	font-size: 12px;
	font-weight: 600;
	color: #475569;
	transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
	text-align: center;
	min-height: 72px;
}
.announce-prio-option label i { font-size: 1.25rem; }
.announce-prio-option input:checked + label {
	border-color: var(--prio-color, #0d6efd);
	background: var(--prio-bg, #eff6ff);
	color: #0f172a;
	box-shadow: 0 0 0 1px var(--prio-color, #0d6efd);
}
.announce-prio-option--normal { --prio-color: #2563eb; --prio-bg: #eff6ff; }
.announce-prio-option--high { --prio-color: #d97706; --prio-bg: #fffbeb; }
.announce-prio-option--urgent { --prio-color: #dc2626; --prio-bg: #fef2f2; }
.announce-prio-option--low { --prio-color: #64748b; --prio-bg: #f1f5f9; }
.announce-audience-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 8px;
}
@media (min-width: 768px) {
	.announce-audience-grid { grid-template-columns: repeat(4, 1fr); }
}
.announce-audience-tile input {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}
.announce-audience-tile {
	position: relative;
}
.announce-audience-tile label {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 6px;
	padding: 14px 10px;
	border: 2px solid #e2e8f0;
	border-radius: 12px;
	background: #fff;
	cursor: pointer;
	font-size: 12px;
	font-weight: 600;
	color: #334155;
	transition: all 0.15s;
	text-align: center;
}
.announce-audience-tile label i {
	font-size: 1.35rem;
	color: #64748b;
}
.announce-audience-tile input:checked + label {
	border-color: #2563eb;
	background: #eff6ff;
	color: #1e40af;
}
.announce-audience-tile input:checked + label i { color: #2563eb; }
.announce-sidebar-card {
	border: 1px solid #e8ecf1;
	border-radius: 16px;
	background: #f8fafc;
	padding: 1.25rem 1.5rem;
}
.announce-user-pick {
	border: 1px dashed #cbd5e1;
	border-radius: 12px;
	padding: 1rem;
	background: #fff;
}
.announce-selected-user {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 6px 10px;
	border-radius: 999px;
	background: #e0f2fe;
	color: #0369a1;
	font-size: 12px;
	font-weight: 600;
	margin: 4px 4px 0 0;
}
.announce-selected-user button {
	border: 0;
	background: transparent;
	color: #0369a1;
	padding: 0 2px;
	line-height: 1;
	opacity: 0.8;
}
.announce-selected-user button:hover { opacity: 1; }
.announce-progress {
	border-radius: 12px;
	background: #fffbeb;
	border: 1px solid #fde68a;
	padding: 12px 14px;
}
.announce-message-area textarea {
	min-height: 160px;
	resize: vertical;
	font-size: 15px;
	line-height: 1.5;
}

/* Modal editar aviso (central) — scroll no mobile */
.edit-announcement-modal .modal-content {
	display: flex;
	flex-direction: column;
	max-height: min(92vh, 920px);
	max-height: min(92dvh, 920px);
}
.edit-announcement-modal__form {
	display: flex;
	flex-direction: column;
	flex: 1 1 auto;
	min-height: 0;
}
.edit-announcement-modal__body {
	overflow-y: auto;
	-webkit-overflow-scrolling: touch;
	flex: 1 1 auto;
	min-height: 0;
	max-height: none;
}
.edit-announcement-modal__footer {
	border-top: 1px solid var(--bs-border-color, #dee2e6);
	background: #fff;
}
@media (max-width: 575.98px) {
	.edit-announcement-modal .modal-content {
		max-height: 100%;
		height: 100%;
		border-radius: 0;
	}
	.edit-announcement-modal__body {
		padding-bottom: 1.25rem;
	}
	.edit-announcement-modal__footer {
		flex-wrap: wrap;
		gap: 0.5rem;
	}
	.edit-announcement-modal__footer .btn {
		flex: 1 1 auto;
	}
}
</style>
