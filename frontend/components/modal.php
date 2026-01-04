<!-- Reusable Modal Component -->
<div id="globalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all">
        <div id="modalHeader" class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-900"></h3>
                <button onclick="closeGlobalModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div id="modalBody" class="px-6 py-4">
            <p id="modalMessage" class="text-gray-700"></p>
        </div>
        <div id="modalFooter" class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button id="modalCancelBtn" onclick="closeGlobalModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors hidden">
                Cancel
            </button>
            <button id="modalConfirmBtn" onclick="closeGlobalModal()" class="px-4 py-2 text-white bg-[#940505] hover:bg-[#7a0404] rounded-lg transition-colors">
                OK
            </button>
        </div>
    </div>
</div>

<script>
    // Global modal functions
    function showModal(options) {
        const {
            title = 'Notification',
            message = '',
            type = 'info', // 'info', 'success', 'error', 'warning', 'confirm'
            onConfirm = null,
            onCancel = null,
            confirmText = 'OK',
            cancelText = 'Cancel'
        } = options;

        const modal = document.getElementById('globalModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn = document.getElementById('modalCancelBtn');

        // Set title and message
        modalTitle.textContent = title;
        modalMessage.textContent = message;

        // Style based on type
        const titleColors = {
            success: 'text-green-600',
            error: 'text-red-600',
            warning: 'text-orange-600',
            info: 'text-blue-600',
            confirm: 'text-gray-900'
        };
        modalTitle.className = `text-xl font-semibold ${titleColors[type] || titleColors.info}`;

        // Configure buttons
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        if (type === 'confirm') {
            cancelBtn.classList.remove('hidden');
        } else {
            cancelBtn.classList.add('hidden');
        }

        // Set up event handlers
        confirmBtn.onclick = () => {
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();
            }
            closeGlobalModal();
        };

        cancelBtn.onclick = () => {
            if (onCancel && typeof onCancel === 'function') {
                onCancel();
            }
            closeGlobalModal();
        };

        // Show modal
        modal.classList.remove('hidden');
    }

    function closeGlobalModal() {
        const modal = document.getElementById('globalModal');
        modal.classList.add('hidden');
    }

    // Convenience functions
    function showSuccess(message, title = 'Success') {
        showModal({ title, message, type: 'success' });
    }

    function showError(message, title = 'Error') {
        showModal({ title, message, type: 'error' });
    }

    function showWarning(message, title = 'Warning') {
        showModal({ title, message, type: 'warning' });
    }

    function showInfo(message, title = 'Information') {
        showModal({ title, message, type: 'info' });
    }

    function showConfirm(message, onConfirm, title = 'Confirm', onCancel = null) {
        showModal({
            title,
            message,
            type: 'confirm',
            onConfirm,
            onCancel,
            confirmText: 'Confirm',
            cancelText: 'Cancel'
        });
    }

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeGlobalModal();
        }
    });

    // Close modal on backdrop click
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('globalModal');
        if (e.target === modal) {
            closeGlobalModal();
        }
    });
</script>
