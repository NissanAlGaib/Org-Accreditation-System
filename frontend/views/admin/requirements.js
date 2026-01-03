const modal = document.getElementById('requirementModal');
const modalTitle = document.getElementById('modalTitle');
const form = document.getElementById('requirementForm');
const submitBtn = document.getElementById('submitBtn');

const requirementIdInput = document.getElementById('requirementId');
const requirementNameInput = document.getElementById('requirementName');
const requirementTypeInput = document.getElementById('requirementType');
const requirementDescInput = document.getElementById('requirementDesc');

let isEditMode = false;

// Load requirements on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadRequirements();
});

async function loadRequirements() {
    const tbody = document.getElementById('requirementsTableBody');
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            const requirements = result.data;
            
            if (requirements.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No requirements added yet
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = requirements.map(req => {
                    const createdDate = req.created_at ? new Date(req.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
                    return `
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    ${escapeHtml(req.requirement_name)}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-semibold">
                                    ${escapeHtml(req.requirement_type)}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate">
                                ${escapeHtml(req.description || '-')}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                ${escapeHtml((req.first_name || '') + ' ' + (req.last_name || ''))}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                ${createdDate}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button onclick='editRequirement(${JSON.stringify(req)})' 
                                            class="text-blue-600 hover:text-blue-800 transition-colors" 
                                            title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteRequirement(${req.requirement_id})" 
                                            class="text-red-500 hover:text-red-700 transition-colors" 
                                            title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-red-500">
                        Error loading requirements: ${result.message || 'Unknown error'}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading requirements:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    Failed to load requirements. Please try again.
                </td>
            </tr>
        `;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openModal() {
    isEditMode = false;
    modalTitle.textContent = 'Add Requirement';
    submitBtn.textContent = 'Add Requirement';
    form.reset();
    requirementIdInput.value = '';
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
}

modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeModal();
    }
});

function editRequirement(requirement) {
    isEditMode = true;
    modalTitle.textContent = 'Edit Requirement';
    submitBtn.textContent = 'Update Requirement';
    
    requirementIdInput.value = requirement.requirement_id;
    requirementNameInput.value = requirement.requirement_name;
    requirementTypeInput.value = requirement.requirement_type;
    requirementDescInput.value = requirement.description || '';
    
    modal.classList.remove('hidden');
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const originalBtnText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = isEditMode ? 'Updating...' : 'Creating...';
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const method = isEditMode ? 'PUT' : 'POST';
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(isEditMode ? 'Requirement updated successfully' : 'Requirement created successfully');
            await loadRequirements();
            closeModal();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Submission Error:', error);
        alert('An error occurred. Check console for details.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});

async function deleteRequirement(requirementId) {
    if (!confirm('Are you sure you want to delete this requirement?')) {
        return;
    }
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php?requirement_id=' + requirementId, {
            method: 'DELETE'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success') {
            alert('Requirement deleted successfully');
            await loadRequirements();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Delete Error:', error);
        alert('An error occurred. Check console for details.');
    }
}
