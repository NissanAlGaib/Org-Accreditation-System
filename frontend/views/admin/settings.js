// Load user data
document.addEventListener('DOMContentLoaded', async () => {
    await loadUserData();
    await loadActiveAcademicYear();
});

async function loadUserData() {
    try {
        const response = await fetch(`/Org-Accreditation-System/backend/api/user_api.php?user_id=${userId}`);
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            document.getElementById('email').value = result.data.email || '';
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

async function loadActiveAcademicYear() {
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/academic_year_api.php?active=1');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            const year = result.data;
            document.getElementById('activeYearText').textContent = `${year.year_start}-${year.year_end}`;
        }
    } catch (error) {
        console.error('Error loading academic year:', error);
    }
}

// Edit Profile
document.getElementById('editProfileBtn').addEventListener('click', () => {
    document.getElementById('firstName').readOnly = false;
    document.getElementById('lastName').readOnly = false;
    document.getElementById('email').readOnly = false;
    document.getElementById('firstName').classList.remove('bg-gray-50');
    document.getElementById('lastName').classList.remove('bg-gray-50');
    document.getElementById('email').classList.remove('bg-gray-50');
    document.getElementById('editButtons').classList.remove('hidden');
    document.getElementById('editProfileBtn').classList.add('hidden');
});

document.getElementById('cancelEditBtn').addEventListener('click', () => {
    document.getElementById('firstName').readOnly = true;
    document.getElementById('lastName').readOnly = true;
    document.getElementById('email').readOnly = true;
    document.getElementById('firstName').classList.add('bg-gray-50');
    document.getElementById('lastName').classList.add('bg-gray-50');
    document.getElementById('email').classList.add('bg-gray-50');
    document.getElementById('editButtons').classList.add('hidden');
    document.getElementById('editProfileBtn').classList.remove('hidden');
    loadUserData();
});

document.getElementById('saveProfileBtn').addEventListener('click', async () => {
    showInfo('Profile update functionality will be implemented.');
});

// Change Password
document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showWarning('Please fill in all fields');
        return;
    }
    
    if (newPassword.length < 8) {
        showWarning('Password must be at least 8 characters long');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showWarning('New passwords do not match');
        return;
    }
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/user_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'change_password',
                new_password: newPassword
            })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Password changed successfully');
            document.getElementById('passwordForm').reset();
        } else {
            showError(result.message || 'Failed to change password');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while changing password');
    }
});

// Audit Logs
async function viewAuditLogs() {
    document.getElementById('auditLogModal').classList.remove('hidden');
    document.getElementById('auditLogContent').innerHTML = 'Loading...';
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php?audit_log=1');
        const result = await response.json();
        
        if (result.status === 'success' && result.data && result.data.length > 0) {
            let html = '<div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Document ID</th><th class="px-4 py-2 text-left">Old Status</th><th class="px-4 py-2 text-left">New Status</th><th class="px-4 py-2 text-left">Changed By</th><th class="px-4 py-2 text-left">Timestamp</th></tr></thead><tbody>';
            
            result.data.forEach(log => {
                html += `<tr class="border-b"><td class="px-4 py-2">${log.document_id}</td><td class="px-4 py-2">${log.old_status}</td><td class="px-4 py-2">${log.new_status}</td><td class="px-4 py-2">${log.changed_by}</td><td class="px-4 py-2">${log.change_timestamp}</td></tr>`;
            });
            
            html += '</tbody></table></div>';
            document.getElementById('auditLogContent').innerHTML = html;
        } else {
            document.getElementById('auditLogContent').innerHTML = '<p class="text-gray-500">No audit logs found</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('auditLogContent').innerHTML = '<p class="text-red-500">Error loading audit logs</p>';
    }
}

function closeAuditModal() {
    document.getElementById('auditLogModal').classList.add('hidden');
}

// Deletion Attempts
async function viewDeletionAttempts() {
    document.getElementById('deletionModal').classList.remove('hidden');
    document.getElementById('deletionLogContent').innerHTML = 'Loading...';
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php?deletion_attempts=1');
        const result = await response.json();
        
        if (result.status === 'success' && result.data && result.data.length > 0) {
            let html = '<div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Document ID</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left">Org ID</th><th class="px-4 py-2 text-left">File Name</th><th class="px-4 py-2 text-left">Timestamp</th></tr></thead><tbody>';
            
            result.data.forEach(log => {
                html += `<tr class="border-b"><td class="px-4 py-2">${log.document_id}</td><td class="px-4 py-2"><span class="bg-red-100 text-red-800 px-2 py-1 rounded">${log.document_status}</span></td><td class="px-4 py-2">${log.org_id}</td><td class="px-4 py-2">${log.file_name}</td><td class="px-4 py-2">${log.attempt_timestamp}</td></tr>`;
            });
            
            html += '</tbody></table></div>';
            document.getElementById('deletionLogContent').innerHTML = html;
        } else {
            document.getElementById('deletionLogContent').innerHTML = '<p class="text-gray-500">No deletion attempts found</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('deletionLogContent').innerHTML = '<p class="text-red-500">Error loading deletion logs</p>';
    }
}

function closeDeletionModal() {
    document.getElementById('deletionModal').classList.add('hidden');
}
