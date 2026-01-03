// Load documents on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadDocuments();
});

async function loadDocuments() {
    const tbody = document.getElementById('documentsTableBody');
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php?grouped=true', {
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
                tbody.innerHTML = documents.map(org => {
                    const total = org.total_documents || 0;
                    const verified = org.verified_count || 0;
                    const pending = org.pending_count || 0;
                    const returned = org.returned_count || 0;
                    const progress = total > 0 ? Math.round((verified / total) * 100) : 0;
                    
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
