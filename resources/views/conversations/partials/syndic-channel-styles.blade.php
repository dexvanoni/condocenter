<style>
	.comm-hub.comm-hub--syndic {
		grid-template-columns: 88px min(380px, 32vw) 1fr;
		min-height: calc(100vh - 280px);
	}
	.comm-hub--syndic .comm-hub__inbox {
		display: flex;
		flex-direction: column;
		min-height: 0;
	}
	.comm-hub--syndic .comm-hub__thread {
		display: flex;
		flex-direction: column;
		min-height: 0;
		overflow: hidden;
	}
	.comm-hub--syndic .comm-thread-body {
		flex: 1 1 auto;
		min-height: 0;
	}
	@media (max-width: 991.98px) {
		.comm-hub.comm-hub--syndic {
			grid-template-columns: 1fr;
			grid-template-rows: auto auto minmax(320px, 1fr);
		}
		.comm-hub--syndic .comm-hub__thread {
			min-height: 320px;
		}
	}
	.comm-rail-link {
		text-decoration: none;
		display: block;
	}
	.comm-hub--syndic .comm-send-progress__bar .progress-bar {
		background: linear-gradient(90deg, #0f766e, #14b8a6);
	}
	.comm-hub--syndic .syndic-btn-send { background: #0f766e; border-color: #0f766e; }
	.conv-avatar {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%);
		color: white;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-weight: 600;
		font-size: 14px;
		flex-shrink: 0;
	}
	.conversation-item {
		padding: 12px 16px;
		border-bottom: 1px solid #e9ecef;
		transition: all 0.2s;
		cursor: pointer;
	}
	.conversation-item:hover { background-color: #f8f9fa; }
	.conversation-item.active {
		background-color: #ecfdf5;
		border-left: 3px solid #0f766e;
	}
	.message-bubble {
		padding: 10px 16px;
		border-radius: 18px;
		max-width: 75%;
		margin-bottom: 12px;
		word-wrap: break-word;
	}
	.message-sent {
		background-color: #0f766e;
		color: white;
		margin-left: auto;
		border-bottom-right-radius: 4px;
	}
	.message-received {
		background-color: #f1f3f5;
		color: #212529;
		margin-right: auto;
		border-bottom-left-radius: 4px;
	}
	.message-timestamp { font-size: 11px; color: #6c757d; margin-top: 4px; }
	#messageContainer { background-color: #fafbfc; padding: 20px; }
	.compose-area {
		border-top: 1px solid #dee2e6;
		background-color: white;
		padding: 16px;
	}
	.tab-content-wrapper {
		min-height: 400px;
		max-height: calc(100vh - 300px);
		overflow-y: auto;
	}
	/* Não usar display:flex no root — quebra o grid da central (rail | inbox | thread) */
	.syndic-chat-root.comm-hub {
		min-height: calc(100vh - 280px);
	}
	.syndic-send-progress { margin-bottom: 10px; }
	.syndic-send-progress__bar { height: 5px; border-radius: 999px; background: #e2e8f0; }
	.syndic-send-progress__bar .progress-bar { border-radius: 999px; }
	.syndic-compose-area.is-sending { opacity: 0.92; pointer-events: none; }
	.message-attachments { font-size: 12px; }
	.message-attachment-item {
		padding: 8px 10px;
		border-radius: 10px;
		background: rgba(15, 23, 42, 0.06);
		margin-top: 4px;
	}
	.message-attachment-item--sent { background: rgba(255, 255, 255, 0.15); }
	.message-attachment-link { text-decoration: none; font-weight: 500; }
	.message-attachment-link--sent { color: #fff !important; }
	.message-attachment-link:not(.message-attachment-link--sent) { color: #0f766e !important; }
	.message-attachment-download { font-size: 11px; }
	.message-attachment-preview {
		max-width: min(260px, 100%);
		max-height: 200px;
		object-fit: contain;
		border: 1px solid rgba(0, 0, 0, 0.08);
	}
	#syndicSendProgress + .syndic-message-form .syndic-btn-send-spinner {
		width: 1rem;
		height: 1rem;
	}
</style>
