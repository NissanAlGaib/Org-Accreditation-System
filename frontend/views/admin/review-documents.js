const returnModal = document.getElementById('returnModal');
const returnForm = document.getElementById('returnForm');
const documentIdInput = document.getElementById('documentIdInput');
const remarksInput = document.getElementById('remarksInput');

// Load documents on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadOrganizationData();
    await loadDocuments();
});

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
            const documents = result.data;
            
            if (documents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No documents submitted yet
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = documents.map(doc => {
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
            }
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function openReturnModal(documentId) {
    documentIdInput.value = documentId;
    remarksInput.value = '';
    returnModal.classList.remove('hidden');
}

function closeReturnModal() {
    returnModal.classList.add('hidden');
}

returnModal.addEventListener('click', (e) => {
    if (e.target === returnModal) {
        closeReturnModal();
    }
});

returnForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const documentId = documentIdInput.value;
    const remarks = remarksInput.value;
    
    await updateStatus(documentId, 'returned', remarks);
    closeReturnModal();
});

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
            alert('Document status updated successfully');
            await loadDocuments();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Update Error:', error);
        alert('An error occurred. Check console for details.');
    }
}

function viewDocument(documentId) {
    alert('Document viewer not implemented yet. Document ID: ' + documentId);
}
