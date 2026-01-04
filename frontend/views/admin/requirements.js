const modal = document.getElementById('requirementModal');
const modalTitle = document.getElementById('modalTitle');
const form = document.getElementById('requirementForm');
const submitBtn = document.getElementById('submitBtn');

const requirementIdInput = document.getElementById('requirementId');
const requirementNameInput = document.getElementById('requirementName');
const requirementTypeInput = document.getElementById('requirementType');
const requirementDescInput = document.getElementById('requirementDesc');

let isEditMode = false;

// Pagination state
let currentPage = 1;
let itemsPerPage = 10;
let allRequirements = [];
let filteredRequirements = [];
let searchTerm = '';
let typeFilter = '';

// Load requirements on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadRequirements();
});

function handleItemsPerPageChange() {
    itemsPerPage = parseInt(document.getElementById('itemsPerPageSelect').value);
    currentPage = 1; // Reset to first page
    displayPage(currentPage);
}

function handleSearch() {
    searchTerm = document.getElementById('searchInput').value.toLowerCase();
    applyFilters();
}

function handleFilter() {
    typeFilter = document.getElementById('typeFilter').value;
    applyFilters();
}

function applyFilters() {
    filteredRequirements = allRequirements.filter(req => {
        const matchesSearch = searchTerm === '' || 
            req.requirement_name?.toLowerCase().includes(searchTerm) ||
            req.description?.toLowerCase().includes(searchTerm) ||
            (req.first_name + ' ' + req.last_name)?.toLowerCase().includes(searchTerm);
        
        const matchesType = typeFilter === '' || req.requirement_type === typeFilter;
        
        return matchesSearch && matchesType;
    });
    
    currentPage = 1; // Reset to first page when filtering
    displayPage(currentPage);
}

async function loadRequirements() {
    const tbody = document.getElementById('requirementsTableBody');
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            allRequirements = result.data;
            filteredRequirements = allRequirements; // Initialize filtered list
            displayPage(currentPage);
            
        } else {
            const tbody = document.getElementById('requirementsTableBody');
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
        const tbody = document.getElementById('requirementsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    Failed to load requirements. Please try again.
                </td>
            </tr>
        `;
    }
}

function displayPage(page) {
    const tbody = document.getElementById('requirementsTableBody');
    const requirements = filteredRequirements; // Use filtered list
    
    if (requirements.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    ${searchTerm || typeFilter ? 'No requirements match your search criteria' : 'No requirements added yet'}
                </td>
            </tr>
        `;
        updatePaginationControls(0);
        return;
    }
    
    // Calculate pagination
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedReqs = requirements.slice(startIndex, endIndex);
    
    // Render table rows
    tbody.innerHTML = paginatedReqs.map(req => {
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
    
    // Update pagination controls
    updatePaginationControls(requirements.length);
}

function updatePaginationControls(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById('paginationControls');
    
    if (!paginationContainer) return;
    
    // Always show pagination controls
    let paginationHTML = `
        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                Showing ${totalItems > 0 ? ((currentPage - 1) * itemsPerPage + 1) : 0} to ${Math.min(currentPage * itemsPerPage, totalItems)} of ${totalItems} requirements
            </div>
            <div class="flex gap-2">
    `;
    
    // Previous button
    paginationHTML += `
        <button onclick="changePage(${currentPage - 1})" 
                ${currentPage === 1 || totalPages === 0 ? 'disabled' : ''}
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors ${currentPage === 1 || totalPages === 0 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'}">
            Previous
        </button>
    `;
    
    // Page numbers - only show if there are pages
    if (totalPages > 0) {
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            paginationHTML += `
                <button onclick="changePage(1)" class="px-4 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">1</button>
            `;
            if (startPage > 2) {
                paginationHTML += `<span class="px-3 py-2 text-gray-500">...</span>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button onclick="changePage(${i})" 
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors ${i === currentPage ? 'bg-[#940505] text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'}">
                    ${i}
                </button>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<span class="px-3 py-2 text-gray-500">...</span>`;
            }
            paginationHTML += `
                <button onclick="changePage(${totalPages})" class="px-4 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">${totalPages}</button>
            `;
        }
    }
    
    // Next button
    paginationHTML += `
        <button onclick="changePage(${currentPage + 1})" 
                ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors ${currentPage === totalPages || totalPages === 0 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'}">
            Next
        </button>
    `;
    
    paginationHTML += `
            </div>
        </div>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredRequirements.length / itemsPerPage); // Use filtered list
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayPage(currentPage);
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
            await showSuccess(isEditMode ? 'Requirement updated successfully!' : 'Requirement created successfully!');
            await loadRequirements();
            closeModal();
        } else {
            await showError('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Submission Error:', error);
        await showError('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});

async function deleteRequirement(requirementId) {
    const confirmed = await showConfirm(
        'Delete this requirement?',
        'This action cannot be undone. All associated documents may be affected.'
    );
    if (!confirmed) {
        return;
    }
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php?requirement_id=' + requirementId, {
            method: 'DELETE'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Requirement deleted successfully');
            await loadRequirements();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Delete Error:', error);
        showError('An error occurred. Please try again.');
    }
}
