// State management
let allDocuments = [];
let filteredDocuments = [];
let currentPage = 1;
let itemsPerPage = 20;
let searchQuery = '';
let statusFilter = '';
let sortBy = 'newest';

// Modal elements
const viewerModal = document.getElementById('viewerModal');
const returnModal = document.getElementById('returnModal');
const returnForm = document.getElementById('returnForm');
const documentIdInput = document.getElementById('documentIdInput');
const remarksInput = document.getElementById('remarksInput');

// Filter elements
const searchInput = document.getElementById('searchInput');
const statusFilterSelect = document.getElementById('statusFilter');
const sortSelect = document.getElementById('sortSelect');
const perPageSelect = document.getElementById('perPageSelect');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');

// Load documents on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadOrganizationData();
    await loadDocuments();
    setupEventListeners();
});

function setupEventListeners() {
    // Search with debounce
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = e.target.value.toLowerCase();
            applyFiltersAndRender();
        }, 300);
    });
    
    // Status filter
    statusFilterSelect.addEventListener('change', (e) => {
        statusFilter = e.target.value;
        applyFiltersAndRender();
    });
    
    // Sort select
    sortSelect.addEventListener('change', (e) => {
        sortBy = e.target.value;
        applyFiltersAndRender();
    });
    
    // Per page select
    perPageSelect.addEventListener('change', (e) => {
        itemsPerPage = e.target.value === 'all' ? Infinity : parseInt(e.target.value);
        currentPage = 1;
        renderDocuments();
    });
    
    // Clear filters
    clearFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        statusFilterSelect.value = '';
        sortSelect.value = 'newest';
        searchQuery = '';
        statusFilter = '';
        sortBy = 'newest';
        applyFiltersAndRender();
    });
    
    // Modal close on outside click
    viewerModal.addEventListener('click', (e) => {
        if (e.target === viewerModal) {
            closeViewerModal();
        }
    });
    
    returnModal.addEventListener('click', (e) => {
        if (e.target === returnModal) {
            closeReturnModal();
        }
    });
    
    // Return form submit
    returnForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const documentId = documentIdInput.value;
        const remarks = remarksInput.value;
        await updateStatus(documentId, 'returned', remarks);
        closeReturnModal();
    });
}

async function loadOrganizationData() {
    try {
        const response = await fetch(`/Org-Accreditation-System/backend/api/organization_api.php?org_id=${orgId}`, {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            const orgNameTitle = document.getElementById('orgNameTitle');
            orgNameTitle.textContent = result.data.org_name || 'Organization';
            document.title = `Review Documents - ${result.data.org_name || 'Organization'}`;
        }
    } catch (error) {
        console.error('Error loading organization data:', error);
    }
}

async function loadDocuments() {
    const tbody = document.getElementById('documentsTableBody');
    
    try {
        const response = await fetch(`/Org-Accreditation-System/backend/api/document_api.php?org_id=${orgId}`, {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            allDocuments = result.data;
            applyFiltersAndRender();
            updateQuickStats();
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
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-red-500">
                    Failed to load documents. Please try again.
                </td>
            </tr>
        `;
    }
}

function applyFiltersAndRender() {
    // Apply filters
    filteredDocuments = allDocuments.filter(doc => {
        // Search filter
        if (searchQuery) {
            const searchableText = `${doc.requirement_name} ${doc.file_name} ${doc.remarks || ''}`.toLowerCase();
            if (!searchableText.includes(searchQuery)) {
                return false;
            }
        }
        
        // Status filter
        if (statusFilter && doc.status !== statusFilter) {
            return false;
        }
        
        return true;
    });
    
    // Apply sorting
    filteredDocuments.sort((a, b) => {
        switch (sortBy) {
            case 'newest':
                return new Date(b.submitted_at) - new Date(a.submitted_at);
            case 'oldest':
                return new Date(a.submitted_at) - new Date(b.submitted_at);
            case 'requirement':
                return (a.requirement_name || '').localeCompare(b.requirement_name || '');
            case 'status':
                return (a.status || '').localeCompare(b.status || '');
            default:
                return 0;
        }
    });
    
    currentPage = 1;
    renderDocuments();
}

function renderDocuments() {
    const tbody = document.getElementById('documentsTableBody');
    
    if (filteredDocuments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    ${searchQuery || statusFilter ? 'No documents match your filters' : 'No documents submitted yet'}
                </td>
            </tr>
        `;
        updateResultsCount(0, 0, 0);
        document.getElementById('paginationContainer').classList.add('hidden');
        return;
    }
    
    // Pagination
    const totalItems = filteredDocuments.length;
    const totalPages = itemsPerPage === Infinity ? 1 : Math.ceil(totalItems / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = itemsPerPage === Infinity ? totalItems : Math.min(startIndex + itemsPerPage, totalItems);
    const paginatedDocs = filteredDocuments.slice(startIndex, endIndex);
    
    // Render table
    tbody.innerHTML = paginatedDocs.map(doc => {
        const status = doc.status || 'pending';
        const statusColors = {
            'verified': 'bg-green-100 text-green-700',
            'pending': 'bg-yellow-100 text-yellow-700',
            'returned': 'bg-red-100 text-red-700'
        };
        const statusColor = statusColors[status] || 'bg-gray-100 text-gray-700';
        
        const submittedDate = doc.submitted_at ? new Date(doc.submitted_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
        
        return `
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 font-medium text-gray-900">
                    ${escapeHtml(doc.requirement_name || 'N/A')}
                </td>
                <td class="px-6 py-4">
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">
                        ${escapeHtml(doc.requirement_type || 'N/A')}
                    </span>
                </td>
                <td class="px-6 py-4">
                    ${escapeHtml(doc.file_name || 'document.pdf')}
                </td>
                <td class="px-6 py-4 text-gray-500">
                    ${submittedDate}
                </td>
                <td class="px-6 py-4">
                    <span class="${statusColor} px-3 py-1 rounded-full text-xs font-semibold">
                        ${capitalize(status)}
                    </span>
                </td>
                <td class="px-6 py-4 max-w-xs truncate">
                    ${escapeHtml(doc.remarks || '-')}
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="updateStatus(${doc.document_id}, 'verified')" 
                                class="text-green-600 hover:text-green-800 transition-colors" 
                                title="Verify">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                        <button onclick="openReturnModal(${doc.document_id})" 
                                class="text-red-600 hover:text-red-800 transition-colors" 
                                title="Return for revision">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                        </button>
                        <button onclick="viewDocument(${doc.document_id})" 
                                class="text-blue-600 hover:text-blue-800 transition-colors" 
                                title="View document">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    
    updateResultsCount(startIndex + 1, endIndex, totalItems);
    renderPagination(totalPages);
}

function updateResultsCount(start, end, total) {
    const resultsCount = document.getElementById('resultsCount');
    if (total === 0) {
        resultsCount.textContent = 'No documents found';
    } else {
        resultsCount.textContent = `Showing ${start} to ${end} of ${total} documents`;
    }
}

function renderPagination(totalPages) {
    const container = document.getElementById('paginationContainer');
    const buttonsContainer = document.getElementById('paginationButtons');
    
    if (totalPages <= 1) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    
    let buttons = [];
    
    // Previous button
    buttons.push(`
        <button onclick="goToPage(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''}
                class="px-3 py-1 rounded-lg border ${currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100'} transition-colors">
            Previous
        </button>
    `);
    
    // Page numbers
    const maxPageButtons = 7;
    let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);
    
    if (endPage - startPage < maxPageButtons - 1) {
        startPage = Math.max(1, endPage - maxPageButtons + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        buttons.push(`
            <button onclick="goToPage(${i})"
                    class="px-3 py-1 rounded-lg ${i === currentPage ? 'bg-[#940505] text-white' : 'bg-white text-gray-700 hover:bg-gray-100'} border transition-colors">
                ${i}
            </button>
        `);
    }
    
    // Next button
    buttons.push(`
        <button onclick="goToPage(${currentPage + 1})" 
                ${currentPage === totalPages ? 'disabled' : ''}
                class="px-3 py-1 rounded-lg border ${currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100'} transition-colors">
            Next
        </button>
    `);
    
    buttonsContainer.innerHTML = buttons.join('');
}

function goToPage(page) {
    const totalPages = Math.ceil(filteredDocuments.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderDocuments();
}

function updateQuickStats() {
    const stats = {
        total: allDocuments.length,
        pending: allDocuments.filter(d => d.status === 'pending').length,
        verified: allDocuments.filter(d => d.status === 'verified').length,
        returned: allDocuments.filter(d => d.status === 'returned').length
    };
    
    const quickStats = document.getElementById('quickStats');
    quickStats.innerHTML = `
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600">Total Documents</div>
            <div class="text-2xl font-bold text-gray-900">${stats.total}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4">
            <div class="text-sm text-yellow-700">Pending Review</div>
            <div class="text-2xl font-bold text-yellow-900">${stats.pending}</div>
        </div>
        <div class="bg-green-50 rounded-lg border border-green-200 p-4">
            <div class="text-sm text-green-700">Verified</div>
            <div class="text-2xl font-bold text-green-900">${stats.verified}</div>
        </div>
        <div class="bg-red-50 rounded-lg border border-red-200 p-4">
            <div class="text-sm text-red-700">Returned</div>
            <div class="text-2xl font-bold text-red-900">${stats.returned}</div>
        </div>
    `;
}

async function viewDocument(documentId) {
    try {
        const doc = allDocuments.find(d => d.document_id === documentId);
        if (!doc) {
            showError('Document not found');
            return;
        }
        
        // Show modal
        viewerModal.classList.remove('hidden');
        
        // Set title
        document.getElementById('viewerTitle').textContent = doc.file_name || 'Document Viewer';
        
        // Render metadata
        const metadata = document.getElementById('viewerMetadata');
        const statusColors = {
            'verified': 'bg-green-100 text-green-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'returned': 'bg-red-100 text-red-800'
        };
        const statusColor = statusColors[doc.status] || 'bg-gray-100 text-gray-800';
        
        metadata.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Requirement:</span>
                    <span class="text-gray-600">${escapeHtml(doc.requirement_name || 'N/A')} (${escapeHtml(doc.requirement_type || 'N/A')})</span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Submitted:</span>
                    <span class="text-gray-600">${new Date(doc.submitted_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Status:</span>
                    <span class="${statusColor} text-xs font-semibold px-3 py-1 rounded-full ml-2">
                        ${capitalize(doc.status)}
                    </span>
                </div>
                ${doc.remarks ? `
                <div class="md:col-span-2">
                    <span class="font-semibold text-gray-700">Remarks:</span>
                    <span class="text-gray-600">${escapeHtml(doc.remarks)}</span>
                </div>
                ` : ''}
            </div>
            <div class="mt-4 flex gap-2">
                <a href="${escapeHtml(doc.file_path)}" 
                   download="${escapeHtml(doc.file_name)}"
                   class="bg-[#940505] text-white px-4 py-2 rounded-lg hover:bg-red-800 transition-colors text-sm">
                    Download
                </a>
            </div>
        `;
        
        // Render document content
        const content = document.getElementById('viewerContent');
        const fileExtension = (doc.file_name || '').split('.').pop().toLowerCase();
        
        if (fileExtension === 'pdf') {
            content.innerHTML = `
                <iframe src="${escapeHtml(doc.file_path)}" 
                        class="w-full border-0 rounded-lg" 
                        style="height: 900px;">
                </iframe>
            `;
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            content.innerHTML = `
                <img src="${escapeHtml(doc.file_path)}" 
                     alt="${escapeHtml(doc.file_name)}"
                     class="max-w-full h-auto mx-auto rounded-lg shadow-lg">
            `;
        } else {
            content.innerHTML = `
                <div class="text-center py-20">
                    <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-600 mb-4">Preview not available for .${fileExtension} files</p>
                    <a href="${escapeHtml(doc.file_path)}" 
                       download="${escapeHtml(doc.file_name)}"
                       class="bg-[#940505] text-white px-6 py-3 rounded-lg hover:bg-red-800 transition-colors inline-block">
                        Download File
                    </a>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error viewing document:', error);
        showError('Failed to load document viewer');
    }
}

function closeViewerModal() {
    viewerModal.classList.add('hidden');
}

function openReturnModal(documentId) {
    documentIdInput.value = documentId;
    remarksInput.value = '';
    returnModal.classList.remove('hidden');
}

function closeReturnModal() {
    returnModal.classList.add('hidden');
}

async function updateStatus(documentId, status, remarks = null) {
    try {
        const data = {
            document_id: documentId,
            status: status
        };
        
        if (remarks) {
            data.remarks = remarks;
        }
        
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Document status updated successfully');
            await loadDocuments();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Update Error:', error);
        showError('An error occurred. Please try again.');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
