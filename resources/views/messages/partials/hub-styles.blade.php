<style>
.comm-hub-page { --comm-rail-w: 92px; --comm-inbox-w: min(380px, 34vw); }
.comm-hub {
	display: grid;
	grid-template-columns: var(--comm-rail-w) var(--comm-inbox-w) 1fr;
	min-height: calc(100vh - 200px);
	background: #fff;
	border-radius: 16px;
	overflow: hidden;
	border: 1px solid #e8ecf1;
}
@media (max-width: 991.98px) {
	.comm-hub {
		grid-template-columns: 1fr;
		grid-template-rows: auto auto 1fr;
		min-height: auto;
	}
	.comm-hub__rail {
		flex-direction: row !important;
		border-right: 0 !important;
		border-bottom: 1px solid #e8ecf1;
		overflow-x: auto;
	}
	.comm-rail-btn { min-width: 110px; }
	.comm-hub__inbox { max-height: 42vh; border-right: 0 !important; border-bottom: 1px solid #e8ecf1; }
}
.comm-hub__rail {
	display: flex;
	flex-direction: column;
	gap: 6px;
	padding: 10px 8px;
	background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
	border-right: 1px solid #0f172a;
}
.comm-rail-btn {
	border: 0;
	background: transparent;
	color: #94a3b8;
	border-radius: 12px;
	padding: 10px 6px;
	text-align: center;
	font-size: 11px;
	line-height: 1.2;
	transition: background 0.15s, color 0.15s;
}
.comm-rail-btn i { display: block; font-size: 1.35rem; margin-bottom: 4px; }
.comm-rail-btn span { display: block; font-weight: 600; color: #e2e8f0; font-size: 11px; }
.comm-rail-btn small { display: none; }
.comm-rail-btn:hover, .comm-rail-btn.active {
	background: rgba(59, 130, 246, 0.2);
	color: #fff;
}
.comm-rail-btn.active span { color: #fff; }
.comm-rail-btn--syndic.active { background: rgba(16, 185, 129, 0.25); }
.comm-hub__inbox {
	border-right: 1px solid #e8ecf1;
	background: #f8fafc;
	min-width: 0;
}
.comm-inbox-header {
	padding: 14px 14px 10px;
	background: #fff;
	border-bottom: 1px solid #e8ecf1;
}
.comm-filter-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.comm-filter-chips .chip {
	border: 1px solid #e2e8f0;
	background: #fff;
	border-radius: 999px;
	padding: 4px 10px;
	font-size: 11px;
	font-weight: 500;
	color: #475569;
	cursor: pointer;
}
.comm-filter-chips .chip.active {
	background: #0d6efd;
	border-color: #0d6efd;
	color: #fff;
}
.comm-inbox-list {
	overflow-y: auto;
	overflow-x: hidden;
	padding: 8px 10px 12px;
}
.inbox-group-label {
	font-size: 10px;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	color: #94a3b8;
	font-weight: 700;
	padding: 10px 6px 4px;
}
.inbox-item {
	display: flex;
	gap: 10px;
	padding: 10px 10px;
	border-radius: 12px;
	cursor: pointer;
	border: 1px solid transparent;
	background: #fff;
	margin-bottom: 6px;
	transition: border-color 0.15s, box-shadow 0.15s;
	min-width: 0;
}
.inbox-item:hover { border-color: #dbeafe; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05); }
.inbox-item.active {
	border-color: #3b82f6;
	background: #eff6ff;
	box-shadow: 0 0 0 1px #93c5fd;
}
.inbox-item__avatar {
	width: 40px;
	height: 40px;
	border-radius: 12px;
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 13px;
	color: #fff;
	background: linear-gradient(135deg, #6366f1, #8b5cf6);
}
.inbox-item__body { min-width: 0; flex: 1; }
.inbox-item__top {
	display: flex;
	justify-content: space-between;
	align-items: baseline;
	gap: 8px;
	margin-bottom: 2px;
}
.inbox-item__title {
	font-weight: 600;
	font-size: 13px;
	color: #0f172a;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.inbox-item__time {
	font-size: 10px;
	color: #94a3b8;
	flex-shrink: 0;
}
.inbox-item__preview {
	font-size: 12px;
	color: #64748b;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	margin-bottom: 6px;
}
.inbox-item__meta { display: flex; flex-wrap: wrap; gap: 4px; }
.inbox-status {
	font-size: 10px;
	font-weight: 600;
	padding: 2px 8px;
	border-radius: 999px;
}
.inbox-status--awaiting_me { background: #fef3c7; color: #b45309; }
.inbox-status--awaiting_other { background: #e0f2fe; color: #0369a1; }
.inbox-status--awaiting_syndic { background: #fce7f3; color: #be185d; }
.inbox-status--closed { background: #f1f5f9; color: #64748b; }
.inbox-status--open, .inbox-status--responded { background: #dcfce7; color: #15803d; }
.inbox-status--announcement_active { background: #fef9c3; color: #a16207; }
.inbox-priority {
	font-size: 9px;
	text-transform: uppercase;
	padding: 2px 6px;
	border-radius: 4px;
	background: #f1f5f9;
	color: #64748b;
}
.inbox-priority--urgent { background: #fee2e2; color: #b91c1c; }
.inbox-priority--high { background: #ffedd5; color: #c2410c; }
.comm-hub__thread { min-width: 0; background: #fff; }
.comm-thread-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	padding: 12px 16px;
	border-bottom: 1px solid #e8ecf1;
}
.comm-thread-body {
	flex: 1;
	overflow-y: auto;
	background: #f8fafc;
	min-height: 280px;
}
.comm-thread-empty {
	height: 100%;
	min-height: 240px;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	color: #94a3b8;
	text-align: center;
	padding: 2rem;
}
.comm-thread-empty i { font-size: 2.5rem; margin-bottom: 0.75rem; }
.comm-thread-compose {
	padding: 12px 16px;
	border-top: 1px solid #e8ecf1;
	background: #fff;
}
.comm-send-progress {
	margin-bottom: 10px;
}
.comm-send-progress__bar {
	height: 5px;
	border-radius: 999px;
	background: #e2e8f0;
}
.comm-send-progress__bar .progress-bar {
	border-radius: 999px;
	background: linear-gradient(90deg, #2563eb, #3b82f6);
}
.comm-thread-compose.is-sending {
	opacity: 0.92;
	pointer-events: none;
}
.comm-thread-compose.is-sending #messageInput {
	background: #f8fafc;
}
#btnSend .btn-send-spinner {
	width: 1rem;
	height: 1rem;
}
.message-bubble {
	padding: 10px 14px;
	border-radius: 16px;
	max-width: min(75%, 520px);
	margin-bottom: 10px;
	word-wrap: break-word;
	font-size: 14px;
}
.message-sent {
	background: #0d6efd;
	color: #fff;
	margin-left: auto;
	border-bottom-right-radius: 4px;
}
.message-received {
	background: #fff;
	border: 1px solid #e2e8f0;
	color: #1e293b;
	margin-right: auto;
	border-bottom-left-radius: 4px;
}
.message-timestamp { font-size: 10px; opacity: 0.75; margin-top: 4px; }
.message-attachments { font-size: 12px; }
.message-attachment-item {
	padding: 8px 10px;
	border-radius: 10px;
	background: rgba(15, 23, 42, 0.06);
	margin-top: 4px;
}
.message-attachment-item--sent {
	background: rgba(255, 255, 255, 0.15);
}
.message-attachment-link {
	text-decoration: none;
	font-weight: 500;
}
.message-attachment-link--sent { color: #fff !important; }
.message-attachment-link:not(.message-attachment-link--sent) { color: #0d6efd !important; }
.message-attachment-download {
	font-size: 11px;
	opacity: 0.95;
}
.message-attachment-preview {
	max-width: min(260px, 100%);
	max-height: 200px;
	object-fit: contain;
	border: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
