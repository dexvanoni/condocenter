<style>
.users-page .users-page__header h1 {
	font-size: 1.35rem;
	font-weight: 700;
	letter-spacing: -0.02em;
}
.users-toolbar {
	border: 1px solid #e8ecf1;
	border-radius: 14px;
	background: #fff;
	padding: 0.85rem 1rem;
	box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}
.users-toolbar .form-control,
.users-toolbar .form-select {
	font-size: 0.875rem;
	border-color: #e2e8f0;
}
.users-toolbar__meta {
	font-size: 0.75rem;
	color: #64748b;
}
.users-table-wrap {
	border: 1px solid #e8ecf1;
	border-radius: 14px;
	background: #fff;
	overflow: hidden;
	box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}
.users-table {
	margin-bottom: 0;
	font-size: 0.875rem;
}
.users-table thead th {
	font-size: 0.7rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	color: #64748b;
	font-weight: 600;
	background: #f8fafc;
	border-bottom: 1px solid #e8ecf1;
	padding: 0.65rem 0.75rem;
	white-space: nowrap;
}
.users-table tbody td {
	padding: 0.65rem 0.75rem;
	vertical-align: middle;
	border-color: #f1f5f9;
}
.users-table tbody tr:hover {
	background: #fafbfc;
}
.users-identity {
	display: flex;
	align-items: center;
	gap: 0.65rem;
	min-width: 0;
}
.users-avatar {
	width: 36px;
	height: 36px;
	border-radius: 10px;
	object-fit: cover;
	flex-shrink: 0;
	background: #f1f5f9;
}
.users-avatar--placeholder {
	display: flex;
	align-items: center;
	justify-content: center;
	color: #94a3b8;
	font-size: 1.25rem;
}
.users-identity__name {
	font-weight: 600;
	color: #0f172a;
	font-size: 0.875rem;
	line-height: 1.3;
	display: flex;
	align-items: center;
	gap: 0.35rem;
	flex-wrap: wrap;
}
.users-inquilino-badge {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 1.15rem;
	height: 1.15rem;
	border-radius: 4px;
	background: rgba(13, 148, 136, 0.12);
	color: #0f766e;
	font-size: 0.65rem;
	flex-shrink: 0;
}
.users-identity__sub {
	font-size: 0.75rem;
	color: #64748b;
	line-height: 1.35;
}
.users-role-chips {
	display: flex;
	flex-wrap: wrap;
	gap: 0.25rem;
	max-width: 14rem;
}
.users-role-chips .badge {
	font-size: 0.65rem;
	font-weight: 600;
	padding: 0.2em 0.45em;
	border-radius: 6px;
	background: #eff6ff !important;
	color: #1d4ed8 !important;
	border: 1px solid #dbeafe;
}
.users-status-stack {
	display: flex;
	flex-wrap: wrap;
	gap: 0.25rem;
}
.users-status-stack .badge {
	font-size: 0.65rem;
	font-weight: 600;
}
.users-actions .btn {
	padding: 0.25rem 0.45rem;
	border-color: #e2e8f0;
	color: #475569;
}
.users-actions .btn:hover {
	background: #f8fafc;
}
.users-actions .btn-danger-outline:hover {
	color: #dc2626;
	border-color: #fecaca;
	background: #fef2f2;
}
.users-legend {
	font-size: 0.7rem;
	color: #94a3b8;
	display: flex;
	align-items: center;
	gap: 0.35rem;
}
@media (max-width: 991.98px) {
	.users-table .col-email,
	.users-table .col-cpf {
		display: none;
	}
}
</style>
