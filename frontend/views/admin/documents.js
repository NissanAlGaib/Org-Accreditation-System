// Pagination state
let currentPage = 1;
const itemsPerPage = 10;
let allDocuments = [];
let filteredDocuments = [];
let searchTerm = '';
let progressFilter = '';

// Load documents on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadDocuments();
});

function handleSearch() {
    searchTerm = document.getElementById('searchInput').value.toLowerCase();
    applyFilters();
}

function handleFilter() {
    progressFilter = document.getElementById('progressFilter').value;
    applyFilters();
}

function applyFilters() {
    filteredDocuments = allDocuments.filter(org => {
        const matchesSearch = searchTerm === '' || 
            org.org_name?.toLowerCase().includes(searchTerm);
        
        let matchesProgress = true;
        if (progressFilter) {
            const verified = org.verified_count || 0;
            const totalReqs = org.total_requirements || 1;
            const progress = Math.round((verified / totalReqs) * 100);
            
            switch(progressFilter) {
                case 'complete':
                    matchesProgress = progress === 100;
                    break;
                case 'high':
                    matchesProgress = progress >= 80 && progress < 100;
                    break;
                case 'medium':
                    matchesProgress = progress >= 40 && progress < 80;
                    break;
                case 'low':
                    matchesProgress = progress < 40;
                    break;
            }
        }
        
        return matchesSearch && matchesProgress;
    });
    
    currentPage = 1; // Reset to first page when filtering
    displayPage(currentPage);
}

async function loadDocuments() {
    const tbody = document.getElementById('documentsTableBody');
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php?grouped=true', {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            allDocuments = result.data;
            filteredDocuments = allDocuments; // Initialize filtered list
            displayPage(currentPage);
            
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-red-500">
                        Error loading documents: ${result.message || 'Unknown error'}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading documents:', error);
        const tbody = document.getElementById('documentsTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-red-500">
                    Failed to load documents. Please try again.
                </td>
            </tr>
        `;
    }
}

function displayPage(page) {
    const tbody = document.getElementById('documentsTableBody');
    const documents = filteredDocuments; // Use filtered list
    
    if (documents.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    ${searchTerm || progressFilter ? 'No documents match your search criteria' : 'No documents submitted yet'}
                </td>
            </tr>
        `;
        updatePaginationControls(0);
        return;
    }
    
    // Calculate pagination
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedDocs = documents.slice(startIndex, endIndex);
    
    // Render table rows
    tbody.innerHTML = paginatedDocs.map(org => {
        const total = org.total_documents || 0;
        const verified = org.verified_count || 0;
        const pending = org.pending_count || 0;
        const returned = org.returned_count || 0;
        const totalRequirements = org.total_requirements || 1;
        const progress = Math.round((verified / totalRequirements) * 100);
        
        return `
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 font-medium text-gray-900">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        ${escapeHtml(org.org_name)}
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${total}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${verified}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${pending}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                        ${returned}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: ${progress}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">${progress}%</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <a href="review-documents.php?org_id=${org.org_id}" 
                       class="inline-flex items-center gap-2 bg-[#940505] hover:bg-red-800 text-white text-xs font-medium px-4 py-2 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Review Documents
                    </a>
                </td>
            </tr>
        `;
    }).join('');
    
    // Update pagination controls
    updatePaginationControls(documents.length);
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
    const totalPages = Math.ceil(filteredDocuments.length / itemsPerPage); // Use filtered list
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayPage(currentPage);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
