/**
 * messages.js — client interactions for messages page
 * 
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 * 
 * * Messaging system functionality including:
 * - Conversation threading
 * - Real-time message updates
 * - Message composition
 * - Read status tracking
*/

'use strict';

/**
 * Initialize messaging system when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeConversationList();
    initializeMessageForm();
    initializeMessageActions();
    initializeAutoRefresh();
    scrollToLatestMessage();
});

/**
 * Current conversation state
 */
let currentConversation = {
    userId: null,
    clothingId: null,
    lastMessageId: 0
};

/**
 * Initialize conversation list click handlers
 */
function initializeConversationList() {
    const conversationItems = document.querySelectorAll('.conversation-item');
    
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const clothingId = this.dataset.clothingId || null;
            
            // Update active state
            conversationItems.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            // Mark as read visually
            this.classList.remove('unread');
            const unreadBadge = this.querySelector('.unread-badge');
            if (unreadBadge) {
                unreadBadge.remove();
            }
            
            // Load conversation
            loadConversation(userId, clothingId);
        });
    });
}

/**
 * Load conversation messages
 * @param {number} userId - User ID to load conversation with
 * @param {number|null} clothingId - Optional clothing item context
 */
function loadConversation(userId, clothingId = null) {
    const messagesContainer = document.getElementById('messagesContainer');
    const loadingIndicator = document.getElementById('messagesLoading');
    
    // Update current conversation state
    currentConversation.userId = userId;
    currentConversation.clothingId = clothingId;
    
    // Show loading state
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
    }
    if (messagesContainer) {
        messagesContainer.style.opacity = '0.5';
    }
    
    // Build URL with parameters
    let url = `messages.php?action=getMessages&userId=${userId}`;
    if (clothingId) {
        url += `&clothingId=${clothingId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMessages(data.messages);
                updateRecipientInfo(data.recipient);
                
                // Mark messages as read
                markMessagesAsRead(userId, clothingId);
                
                // Update last message ID for polling
                if (data.messages.length > 0) {
                    currentConversation.lastMessageId = data.messages[data.messages.length - 1].messageID;
                }
            } else {
                showEmptyState('Could not load messages. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error loading conversation:', error);
            showEmptyState('An error occurred. Please try again.');
        })
        .finally(() => {
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            if (messagesContainer) {
                messagesContainer.style.opacity = '1';
            }
        });
}

/**
 * Render messages in the conversation view
 * @param {array} messages - Array of message objects
 */
function renderMessages(messages) {
    const messagesContainer = document.getElementById('messagesContainer');
    if (!messagesContainer) return;
    
    if (messages.length === 0) {
        showEmptyState('No messages yet. Start the conversation!');
        return;
    }
    
    let html = '';
    let lastDate = '';
    
    messages.forEach(message => {
        // Add date separator if needed
        const messageDate = new Date(message.sentAt).toLocaleDateString();
        if (messageDate !== lastDate) {
            html += `<div class="message-date-separator">${formatDateSeparator(message.sentAt)}</div>`;
            lastDate = messageDate;
        }
        
        const isOwn = message.isOwn;
        const time = formatMessageTime(message.sentAt);
        
        html += `
            <div class="message ${isOwn ? 'message-sent' : 'message-received'}" data-id="${message.messageID}">
                ${!isOwn ? `<div class="message-avatar">${message.senderInitial}</div>` : ''}
                <div class="message-content">
                    ${message.subject ? `<div class="message-subject">${escapeHtml(message.subject)}</div>` : ''}
                    <div class="message-body">${escapeHtml(message.messageBody)}</div>
                    <div class="message-meta">
                        <span class="message-time">${time}</span>
                        ${isOwn && message.isRead ? '<span class="message-read"><i class="fas fa-check-double"></i></span>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    messagesContainer.innerHTML = html;
    scrollToLatestMessage();
}

/**
 * Show empty state message
 * @param {string} message - Message to display
 */
function showEmptyState(message) {
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.innerHTML = `
            <div class="messages-empty-state">
                <i class="far fa-comments"></i>
                <p>${message}</p>
            </div>
        `;
    }
}

/**
 * Update recipient information in header
 * @param {object} recipient - Recipient user data
 */
function updateRecipientInfo(recipient) {
    const recipientName = document.getElementById('recipientName');
    const recipientAvatar = document.getElementById('recipientAvatar');
    const recipientStatus = document.getElementById('recipientStatus');
    
    if (recipientName && recipient) {
        recipientName.textContent = recipient.fullName;
    }
    
    if (recipientAvatar && recipient) {
        recipientAvatar.textContent = recipient.fullName.charAt(0).toUpperCase();
    }
    
    if (recipientStatus && recipient) {
        recipientStatus.textContent = recipient.role;
    }
    
    // Show message form
    const messageForm = document.getElementById('messageReplyForm');
    if (messageForm) {
        messageForm.style.display = 'flex';
    }
}

/**
 * Initialize message form submission
 */
function initializeMessageForm() {
    const messageForm = document.getElementById('messageReplyForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendMessage');
    
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }
    
    if (messageInput) {
        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });
        
        // Send on Enter (without Shift)
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }
    
    if (sendButton) {
        sendButton.addEventListener('click', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }
}

/**
 * Send a new message
 */
function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendMessage');
    
    if (!messageInput || !currentConversation.userId) return;
    
    const message = messageInput.value.trim();
    if (!message) return;
    
    // Disable form while sending
    messageInput.disabled = true;
    if (sendButton) {
        sendButton.disabled = true;
        sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'sendMessage');
    formData.append('receiverID', currentConversation.userId);
    formData.append('messageBody', message);
    if (currentConversation.clothingId) {
        formData.append('clothingID', currentConversation.clothingId);
    }
    
    fetch('messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Add message to conversation
            appendMessage(data.message);
            
            // Scroll to new message
            scrollToLatestMessage();
        } else {
            showToast(data.message || 'Failed to send message', 'error');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        showToast('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        messageInput.disabled = false;
        if (sendButton) {
            sendButton.disabled = false;
            sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
        messageInput.focus();
    });
}

/**
 * Append a new message to the conversation
 * @param {object} message - Message object
 */
function appendMessage(message) {
    const messagesContainer = document.getElementById('messagesContainer');
    if (!messagesContainer) return;
    
    // Remove empty state if present
    const emptyState = messagesContainer.querySelector('.messages-empty-state');
    if (emptyState) {
        emptyState.remove();
    }
    
    const time = formatMessageTime(message.sentAt || new Date().toISOString());
    
    const messageHtml = `
        <div class="message message-sent" data-id="${message.messageID}">
            <div class="message-content">
                <div class="message-body">${escapeHtml(message.messageBody)}</div>
                <div class="message-meta">
                    <span class="message-time">${time}</span>
                </div>
            </div>
        </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    
    // Update last message ID
    currentConversation.lastMessageId = message.messageID;
}

/**
 * Initialize message action buttons (delete, etc.)
 */
function initializeMessageActions() {
    // Delete message action
    document.addEventListener('click', function(e) {
        if (e.target.closest('.message-delete')) {
            const messageEl = e.target.closest('.message');
            const messageId = messageEl?.dataset.id;
            
            if (messageId && confirm('Delete this message?')) {
                deleteMessage(messageId, messageEl);
            }
        }
    });
}

/**
 * Delete a message
 * @param {number} messageId - Message ID to delete
 * @param {HTMLElement} element - Message element to remove
 */
function deleteMessage(messageId, element) {
    fetch('messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=deleteMessage&messageID=${messageId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            element.remove();
            showToast('Message deleted', 'success');
        } else {
            showToast(data.message || 'Failed to delete message', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

/**
 * Mark messages as read
 * @param {number} userId - User ID of conversation partner
 * @param {number|null} clothingId - Optional clothing context
 */
function markMessagesAsRead(userId, clothingId) {
    let url = `messages.php?action=markRead&userId=${userId}`;
    if (clothingId) {
        url += `&clothingId=${clothingId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update unread count in navigation if needed
                updateUnreadCount();
            }
        })
        .catch(error => console.error('Error marking as read:', error));
}

/**
 * Update unread message count in navigation
 */
function updateUnreadCount() {
    fetch('messages.php?action=getUnreadCount')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('messagesUnreadBadge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
}

/**
 * Initialize auto-refresh for new messages
 */
function initializeAutoRefresh() {
    // Poll for new messages every 30 seconds
    setInterval(() => {
        if (currentConversation.userId) {
            checkForNewMessages();
        }
    }, 30000);
}

/**
 * Check for new messages in current conversation
 */
function checkForNewMessages() {
    if (!currentConversation.userId) return;
    
    let url = `messages.php?action=checkNew&userId=${currentConversation.userId}&lastId=${currentConversation.lastMessageId}`;
    if (currentConversation.clothingId) {
        url += `&clothingId=${currentConversation.clothingId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(message => {
                    appendReceivedMessage(message);
                });
                currentConversation.lastMessageId = data.messages[data.messages.length - 1].messageID;
                scrollToLatestMessage();
            }
        })
        .catch(error => console.error('Error checking for new messages:', error));
}

/**
 * Append a received message to the conversation
 * @param {object} message - Message object
 */
function appendReceivedMessage(message) {
    const messagesContainer = document.getElementById('messagesContainer');
    if (!messagesContainer) return;
    
    const time = formatMessageTime(message.sentAt);
    
    const messageHtml = `
        <div class="message message-received" data-id="${message.messageID}">
            <div class="message-avatar">${message.senderInitial}</div>
            <div class="message-content">
                ${message.subject ? `<div class="message-subject">${escapeHtml(message.subject)}</div>` : ''}
                <div class="message-body">${escapeHtml(message.messageBody)}</div>
                <div class="message-meta">
                    <span class="message-time">${time}</span>
                </div>
            </div>
        </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    
    // Play notification sound
    playNotificationSound();
}

/**
 * Scroll to the latest message
 */
function scrollToLatestMessage() {
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

/**
 * Format message time for display
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted time string
 */
function formatMessageTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    
    const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    if (diffDays === 0) {
        return timeStr;
    } else if (diffDays === 1) {
        return 'Yesterday ' + timeStr;
    } else if (diffDays < 7) {
        return date.toLocaleDateString([], { weekday: 'short' }) + ' ' + timeStr;
    } else {
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + timeStr;
    }
}

/**
 * Format date separator
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date string
 */
function formatDateSeparator(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        return 'Today';
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' });
    }
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Play notification sound for new messages
 */
function playNotificationSound() {
    const audio = document.getElementById('notificationSound');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(() => {
            // Autoplay blocked, ignore
        });
    }
}

/**
 * Show toast notification
 * @param {string} message - Message to display
 * @param {string} type - Toast type
 */
function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Compose new message
 * @param {number|null} receiverId - Optional receiver ID
 * @param {number|null} clothingId - Optional clothing context
 */
function composeMessage(receiverId = null, clothingId = null) {
    const modal = document.getElementById('composeModal');
    if (!modal) return;
    
    // Pre-fill receiver if provided
    if (receiverId) {
        const receiverSelect = modal.querySelector('#composeReceiver');
        if (receiverSelect) {
            receiverSelect.value = receiverId;
        }
    }
    
    // Pre-fill clothing context if provided
    if (clothingId) {
        const clothingInput = modal.querySelector('#composeClothingId');
        if (clothingInput) {
            clothingInput.value = clothingId;
        }
    }
    
    // Show modal
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

/**
 * Close compose modal
 */
function closeComposeModal() {
    const modal = document.getElementById('composeModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            // Reset form
            const form = modal.querySelector('form');
            if (form) form.reset();
        }, 300);
    }
}

// Make functions available globally
window.loadConversation = loadConversation;
window.composeMessage = composeMessage;
window.closeComposeModal = closeComposeModal;
