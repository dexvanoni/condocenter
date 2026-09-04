@can('manage_transactions')
<div class="sd-action-bar sd-action-bar--sticky">
    <button type="button" class="sd-action-btn sd-action-btn--receive" data-bs-toggle="modal" data-bs-target="#modalRecebimento">
        <i class="bi bi-cash-coin"></i>
        <span>Receber</span>
    </button>
    <button type="button" class="sd-action-btn sd-action-btn--pay" data-bs-toggle="modal" data-bs-target="#modalPagamento">
        <i class="bi bi-cart-check"></i>
        <span>Comprar / Pagar</span>
    </button>
</div>
@endcan
