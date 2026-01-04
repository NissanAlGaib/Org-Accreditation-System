<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['role_id']) || empty($_SESSION['org_id'])) {
    header('Location: /Org-Accreditation-System/frontend/views/auth/login.php');
    exit;
}

// Redirect admin to admin dashboard
if ($_SESSION['role_id'] == 1) {
    header('Location: /Org-Accreditation-System/frontend/views/admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirements - CampusConnect</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/user-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5 overflow-y-auto">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Requirements</p>
                <p class="text-md">Upload and manage your accreditation documents</p>
            </div>

            <!-- Requirements List -->
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="manrope-bold text-xl">Accreditation Requirements</p>
                        <p class="text-sm text-gray-600">Submit documents for each requirement</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select id="itemsPerPage" onchange="changeItemsPerPage()" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                            <option value="5">5 per page</option>
                            <option value="10" selected>10 per page</option>
                            <option value="20">20 per page</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[600px] overflow-y-auto pr-2" id="requirementsContainer">
                    <div class="col-span-full text-center py-8 text-gray-500">
                        Loading requirements...
                    </div>
                </div>
                
                <!-- Pagination Controls -->
                <div id="paginationControls" class="flex justify-between items-center pt-4 border-t border-gray-200" style="display: none;">
                    <div class="text-sm text-gray-600">
                        Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalItems">0</span> requirements
                    </div>
                    <div class="flex gap-2">
                        <button onclick="previousPage()" id="prevBtn" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        <span id="pageNumbers" class="flex gap-1"></span>
                        <button onclick="nextPage()" id="nextBtn" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submitted Documents Section -->
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Submitted Documents</p>
                    <p class="text-sm text-gray-600">View and manage your submitted documents</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">File Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Requirement</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Submitted</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="documentsTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Loading documents...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadRequirements();
            loadSubmittedDocuments();
        });

        let submittedRequirements = new Map(); // Track submitted requirements with their status
        let allRequirements = []; // Store all requirements
        let currentPage = 1;
        let itemsPerPage = 10;

        async function loadRequirements() {
            const container = document.getElementById('requirementsContainer');
            const orgId = <?php echo intval($_SESSION['org_id']); ?>;

            try {
                // Fetch both requirements and submitted documents
                const [reqResponse, docResponse] = await Promise.all([
                    fetch('/Org-Accreditation-System/backend/api/requirement_api.php'),
                    fetch(`/Org-Accreditation-System/backend/api/document_api.php?org_id=${orgId}`)
                ]);

                if (!reqResponse.ok || !docResponse.ok) throw new Error('Network error');
                
                const reqResult = await reqResponse.json();
                const docResult = await docResponse.json();

                if (reqResult.status === 'success' && reqResult.data) {
                    allRequirements = reqResult.data.filter(req => req.is_active == 1);
                    const documents = docResult.status === 'success' ? docResult.data : [];
                    
                    // Build a map of requirement_id to document status
                    submittedRequirements.clear();
                    documents.forEach(doc => {
                        // Only track if not yet in map or if current doc is more recent
                        if (!submittedRequirements.has(doc.requirement_id) || 
                            doc.submitted_at > submittedRequirements.get(doc.requirement_id).submitted_at) {
                            submittedRequirements.set(doc.requirement_id, doc);
                        }
                    });

                    if (allRequirements.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full text-center py-8 text-gray-500">
                                No active requirements found
                            </div>
                        `;
                        document.getElementById('paginationControls').style.display = 'none';
                    } else {
                        displayRequirements();
                    }
                }
            } catch (error) {
                console.error('Error loading requirements:', error);
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-500">
                        Failed to load requirements. Please try again.
                    </div>
                `;
            }
        }

        function displayRequirements() {
            const container = document.getElementById('requirementsContainer');
            const paginationControls = document.getElementById('paginationControls');
            
            // Calculate pagination
            const totalItems = allRequirements.length;
            const showAll = itemsPerPage === 'all';
            const effectiveItemsPerPage = showAll ? totalItems : parseInt(itemsPerPage);
            const totalPages = showAll ? 1 : Math.ceil(totalItems / effectiveItemsPerPage);
            
            // Ensure currentPage is within bounds
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;
            
            const startIdx = showAll ? 0 : (currentPage - 1) * effectiveItemsPerPage;
            const endIdx = showAll ? totalItems : Math.min(startIdx + effectiveItemsPerPage, totalItems);
            const pageRequirements = allRequirements.slice(startIdx, endIdx);
            
            // Render requirements
            if (pageRequirements.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-gray-500">
                        No requirements found
                    </div>
                `;
            } else {
                container.innerHTML = pageRequirements.map(req => {
                    const submittedDoc = submittedRequirements.get(req.requirement_id);
                    const isSubmitted = !!submittedDoc;
                    const isPending = isSubmitted && submittedDoc.status === 'pending';
                    const isVerified = isSubmitted && submittedDoc.status === 'verified';
                    const isReturned = isSubmitted && submittedDoc.status === 'returned';
                    
                    let statusBadge = '';
                    let uploadButton = '';
                    
                    if (isVerified) {
                        statusBadge = '<span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">✓ Verified</span>';
                        uploadButton = `<button disabled class="ml-4 bg-gray-300 text-gray-600 px-6 py-2 rounded-md cursor-not-allowed whitespace-nowrap flex items-center gap-2" title="Already verified">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Verified
                        </button>`;
                    } else if (isPending) {
                        statusBadge = '<span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">⏳ Under Review</span>';
                        uploadButton = `<button disabled class="ml-4 bg-gray-300 text-gray-600 px-6 py-2 rounded-md cursor-not-allowed whitespace-nowrap flex items-center gap-2" title="Document is pending review">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Pending
                        </button>`;
                    } else if (isReturned) {
                        statusBadge = '<span class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">↩ Returned</span>';
                        uploadButton = `<button onclick="uploadDocument(${parseInt(req.requirement_id)}, '${escapeHtml(req.requirement_name)}')" 
                            class="ml-4 bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-white hover:text-orange-600 border border-transparent hover:border-orange-600 transition-colors whitespace-nowrap flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Re-upload
                        </button>`;
                    } else {
                        statusBadge = '<span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">Not Submitted</span>';
                        uploadButton = `<button onclick="uploadDocument(${parseInt(req.requirement_id)}, '${escapeHtml(req.requirement_name)}')" 
                            class="ml-4 bg-[#940505] text-white px-6 py-2 rounded-md hover:bg-white hover:text-[#940505] border border-transparent hover:border-[#940505] transition-colors whitespace-nowrap flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload
                        </button>`;
                    }
                    
                    return `
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    <h3 class="text-lg font-semibold text-gray-900">${escapeHtml(req.requirement_name)}</h3>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                        ${escapeHtml(req.requirement_type)}
                                    </span>
                                    ${statusBadge}
                                </div>
                                <p class="text-sm text-gray-600 mb-2">${escapeHtml(req.description || 'No description provided')}</p>
                                ${isReturned ? `<div class="mt-2 p-3 bg-red-50 border-l-4 border-red-500 rounded">
                                    <p class="text-sm text-red-700"><strong>Remarks:</strong> ${escapeHtml(submittedDoc.remarks || 'No remarks provided')}</p>
                                </div>` : ''}
                            </div>
                            ${uploadButton}
                        </div>
                    </div>
                `;
                }).join('');
            }
            
            // Update pagination controls
            if (showAll || totalPages <= 1) {
                paginationControls.style.display = 'none';
            } else {
                paginationControls.style.display = 'flex';
                document.getElementById('showingStart').textContent = startIdx + 1;
                document.getElementById('showingEnd').textContent = endIdx;
                document.getElementById('totalItems').textContent = totalItems;
                
                // Update page numbers
                const pageNumbers = document.getElementById('pageNumbers');
                pageNumbers.innerHTML = '';
                
                // Show up to 5 page numbers
                const maxButtons = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
                let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                
                if (endPage - startPage < maxButtons - 1) {
                    startPage = Math.max(1, endPage - maxButtons + 1);
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.onclick = () => goToPage(i);
                    btn.className = i === currentPage 
                        ? 'px-4 py-2 text-sm bg-[#940505] text-white rounded-md'
                        : 'px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300';
                    pageNumbers.appendChild(btn);
                }
                
                // Update button states
                document.getElementById('prevBtn').disabled = currentPage === 1;
                document.getElementById('nextBtn').disabled = currentPage === totalPages;
            }
        }

        function changeItemsPerPage() {
            const select = document.getElementById('itemsPerPage');
            itemsPerPage = select.value;
            currentPage = 1;
            displayRequirements();
        }

        function goToPage(page) {
            currentPage = page;
            displayRequirements();
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                displayRequirements();
            }
        }

        function nextPage() {
            const showAll = itemsPerPage === 'all';
            if (showAll) return;
            
            const totalPages = Math.ceil(allRequirements.length / parseInt(itemsPerPage));
            if (currentPage < totalPages) {
                currentPage++;
                displayRequirements();
            }
        }

        async function loadSubmittedDocuments() {
            const tbody = document.getElementById('documentsTableBody');
            const orgId = <?php echo intval($_SESSION['org_id']); ?>;

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
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No documents submitted yet
                                </td>
                            </tr>
                        `;
                    } else {
                        tbody.innerHTML = documents.map(doc => {
                            const statusColors = {
                                'pending': 'bg-yellow-100 text-yellow-800',
                                'verified': 'bg-green-100 text-green-800',
                                'returned': 'bg-red-100 text-red-800'
                            };
                            const statusColor = statusColors[doc.status] || 'bg-gray-100 text-gray-800';
                            
                            return `
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="font-medium text-gray-900">${escapeHtml(doc.file_name)}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">${escapeHtml(doc.requirement_name)}</div>
                                            <div class="text-xs text-gray-500">${escapeHtml(doc.requirement_type)}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="${statusColor} text-xs font-semibold px-3 py-1 rounded-full">
                                            ${escapeHtml(doc.status.charAt(0).toUpperCase() + doc.status.slice(1))}
                                        </span>
                                        ${doc.remarks ? `<div class="text-xs text-gray-600 mt-1">${escapeHtml(doc.remarks)}</div>` : ''}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        ${new Date(doc.submitted_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric' 
                                        })}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="viewDocument(${parseInt(doc.document_id)})" 
                                                class="text-[#940505] hover:text-white hover:bg-[#940505] px-4 py-2 rounded-md border border-[#940505] transition-colors">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading documents:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-red-500">
                            Failed to load documents. Please try again.
                        </td>
                    </tr>
                `;
            }
        }

        function uploadDocument(requirementId, requirementName) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
            
            fileInput.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    return;
                }

                if (!confirm(`Upload "${file.name}" for ${requirementName}?`)) {
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('requirement_id', requirementId);

                // Show loading state
                const container = document.getElementById('requirementsContainer');
                const uploadStatus = document.createElement('div');
                uploadStatus.className = 'fixed top-20 right-4 bg-blue-500 text-white px-6 py-3 rounded-md shadow-lg';
                uploadStatus.textContent = 'Uploading...';
                document.body.appendChild(uploadStatus);

                try {
                    const response = await fetch('/Org-Accreditation-System/backend/api/document_upload_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (result.status === 'success') {
                        uploadStatus.className = 'fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-md shadow-lg';
                        uploadStatus.textContent = 'Document uploaded successfully!';
                        loadRequirements(); // Reload requirements to update status
                        loadSubmittedDocuments();
                    } else {
                        uploadStatus.className = 'fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-md shadow-lg';
                        uploadStatus.textContent = 'Upload failed: ' + result.message;
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    uploadStatus.className = 'fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-md shadow-lg';
                    uploadStatus.textContent = 'Upload failed. Please try again.';
                } finally {
                    setTimeout(() => uploadStatus.remove(), 3000);
                }
            };

            fileInput.click();
        }

        function viewDocument(documentId) {
            window.open(`/Org-Accreditation-System/frontend/views/common/view-document.php?id=${documentId}`, '_blank', 'width=1200,height=800');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
