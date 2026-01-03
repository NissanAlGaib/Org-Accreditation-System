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
                <div>
                    <p class="manrope-bold text-xl">Accreditation Requirements</p>
                    <p class="text-sm text-gray-600">Submit documents for each requirement</p>
                </div>
                
                <div class="grid grid-cols-1 gap-4" id="requirementsContainer">
                    <div class="text-center py-8 text-gray-500">
                        Loading requirements...
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

        async function loadRequirements() {
            const container = document.getElementById('requirementsContainer');

            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
                    method: 'GET'
                });

                if (!response.ok) throw new Error('Network error');
                const result = await response.json();

                if (result.status === 'success' && result.data) {
                    const requirements = result.data.filter(req => req.is_active == 1);

                    if (requirements.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-8 text-gray-500">
                                No active requirements found
                            </div>
                        `;
                    } else {
                        container.innerHTML = requirements.map(req => `
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">${escapeHtml(req.requirement_name)}</h3>
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                                ${escapeHtml(req.requirement_type)}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-4">${escapeHtml(req.description || 'No description provided')}</p>
                                    </div>
                                    <button onclick="uploadDocument(${req.requirement_id}, '${escapeHtml(req.requirement_name)}')" 
                                            class="ml-4 bg-[#940505] text-white px-6 py-2 rounded-md hover:bg-white hover:text-[#940505] border border-transparent hover:border-[#940505] transition-colors whitespace-nowrap flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload
                                    </button>
                                </div>
                            </div>
                        `).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading requirements:', error);
                container.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        Failed to load requirements. Please try again.
                    </div>
                `;
            }
        }

        async function loadSubmittedDocuments() {
            const tbody = document.getElementById('documentsTableBody');
            const orgId = <?php echo $_SESSION['org_id']; ?>;

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
                                        <button onclick="viewDocument(${doc.document_id})" 
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

                const uploadBtn = event.target;
                const originalText = uploadBtn.innerHTML;
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<span>Uploading...</span>';

                try {
                    const response = await fetch('/Org-Accreditation-System/backend/api/document_upload_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (result.status === 'success') {
                        alert('Document uploaded successfully!');
                        loadSubmittedDocuments();
                    } else {
                        alert('Upload failed: ' + result.message);
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                } finally {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalText;
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
