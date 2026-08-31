<style>
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
	.syndic-chat-root {
		height: calc(100vh - 260px);
		display: flex;
		flex-direction: column;
	}
	.syndic-chat-root .card-body { flex: 1; overflow-y: auto; }
</style>
