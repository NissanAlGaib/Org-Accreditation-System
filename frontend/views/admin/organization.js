// Pagination state
let currentPage = 1;
const itemsPerPage = 10;
let allOrganizations = [];
let filteredOrganizations = [];
let totalRequirements = 0;
let searchTerm = '';
let statusFilter = '';

// Load organizations on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadOrganizations();
});

function handleSearch() {
    searchTerm = document.getElementById('searchInput').value.toLowerCase();
    applyFilters();
}

function handleFilter() {
    statusFilter = document.getElementById('statusFilter').value;
    applyFilters();
}

function applyFilters() {
    filteredOrganizations = allOrganizations.filter(org => {
        const matchesSearch = searchTerm === '' || 
            org.org_name?.toLowerCase().includes(searchTerm) ||
            (org.first_name + ' ' + org.last_name)?.toLowerCase().includes(searchTerm) ||
            org.email?.toLowerCase().includes(searchTerm);
        
        const matchesStatus = statusFilter === '' || org.status === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    currentPage = 1; // Reset to first page when filtering
    displayPage(currentPage);
}

async function loadOrganizations() {
    const tbody = document.getElementById('organizationsTableBody');
    
    try {
        // Fetch organizations data
        const orgResponse = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
            method: 'GET'
        });
        
        if (!orgResponse.ok) throw new Error('Network error');
        const orgResult = await orgResponse.json();
        
        // Fetch requirements data to get total count
        const reqResponse = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php');
        const reqResult = await reqResponse.json();
        
        totalRequirements = reqResult.status === 'success' ? 
            (reqResult.data || []).filter(r => r.is_active == 1).length : 0;
        
        if (orgResult.status === 'success' && orgResult.data) {
            allOrganizations = orgResult.data;
            filteredOrganizations = allOrganizations; // Initialize filtered list
            displayPage(currentPage);
            
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-red-500">
                        Error loading organizations: ${orgResult.message || 'Unknown error'}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading organizations:', error);
        const tbody = document.getElementById('organizationsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-red-500">
                    Failed to load organizations. Please try again.
                </td>
            </tr>
        `;
    }
}

function displayPage(page) {
    const tbody = document.getElementById('organizationsTableBody');
    const organizations = filteredOrganizations; // Use filtered list
    
    if (organizations.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    ${searchTerm || statusFilter ? 'No organizations match your search criteria' : 'No organizations registered yet'}
                </td>
            </tr>
        `;
        updatePaginationControls(0);
        return;
    }
    
    // Calculate pagination
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedOrgs = organizations.slice(startIndex, endIndex);
    
    // Render table rows
    tbody.innerHTML = paginatedOrgs.map(org => {
        const statusColors = {
            'accredited': 'bg-[#0e4b68] text-white',
            'pending': 'bg-yellow-500 text-white',
            'active': 'bg-blue-500 text-white',
            'inactive': 'bg-gray-500 text-white'
        };
        const status = org.status || 'pending';
        const statusColor = statusColors[status] || 'bg-gray-500 text-white';
        
        // Calculate completion rate based on verified documents vs total requirements
        const verifiedDocs = parseInt(org.verified_documents) || 0;
        const completionRate = totalRequirements > 0 ? Math.round((verifiedDocs / totalRequirements) * 100) : 0;
        
        // Color code the progress bar
        let progressBarColor = 'bg-red-500'; // <40%
        if (completionRate >= 80) {
            progressBarColor = 'bg-green-500';
        } else if (completionRate >= 40) {
            progressBarColor = 'bg-yellow-500';
        }
        
        return `
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        ${escapeHtml(org.org_name)}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${org.first_name ? `
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-gray-700">${escapeHtml(org.first_name + ' ' + org.last_name)}</span>
                        </div>
                    ` : '<span class="text-gray-400 italic">Not assigned</span>'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${org.email ? `<span class="text-gray-500">${escapeHtml(org.email)}</span>` : '<span class="text-gray-400 italic">-</span>'}
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${org.total_documents || 0}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${org.verified_documents || 0}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${org.pending_documents || 0}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${org.returned_documents || 0}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col gap-1">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="${progressBarColor} h-2.5 rounded-full" style="width: ${completionRate}%"></div>
                        </div>
                        <span class="text-xs text-gray-600 text-center">${completionRate}% (${verifiedDocs}/${totalRequirements})</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="${statusColor} text-xs font-semibold px-3 py-1 rounded-full">
                        ${capitalize(status)}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
    
    // Update pagination controls
    updatePaginationControls(organizations.length);
}

function updatePaginationControls(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = document.getElementById('paginationControls');
    
    if (!paginationContainer || totalPages <= 1) {
        if (paginationContainer) paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = `
        <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-600">
                Showing ${totalItems > 0 ? ((currentPage - 1) * itemsPerPage + 1) : 0} to ${Math.min(currentPage * itemsPerPage, totalItems)} of ${totalItems} organizations
            </div>
            <div class="flex gap-2">
    `;
    
    // Previous button
    paginationHTML += `
        <button onclick="changePage(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''}
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors ${currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'}">
            Previous
        </button>
    `;
    
    // Page numbers
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
    
    // Next button
    paginationHTML += `
        <button onclick="changePage(${currentPage + 1})" 
                ${currentPage === totalPages ? 'disabled' : ''}
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors ${currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'}">
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
    const totalPages = Math.ceil(filteredOrganizations.length / itemsPerPage); // Use filtered list
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayPage(currentPage);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
