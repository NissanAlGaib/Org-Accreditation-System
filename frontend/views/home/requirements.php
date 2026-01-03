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
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Requirements</p>
                <p class="text-md">Upload and manage your accreditation documents</p>
            </div>

            <!-- Requirements List -->
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Accreditation Requirements</p>
                    <p class="text-sm">Submit documents for each requirement</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-semibold">Requirement Name</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Type</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Description</th>
                                    <th scope="col" class="px-6 py-4 font-semibold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="requirementsTableBody" class="divide-y divide-gray-200">
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Loading requirements...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', loadRequirements);

        async function loadRequirements() {
            const tbody = document.getElementById('requirementsTableBody');

            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
                    method: 'GET'
                });

                if (!response.ok) throw new Error('Network error');
                const result = await response.json();

                if (result.status === 'success' && result.data) {
                    const requirements = result.data.filter(req => req.is_active == 1);

                    if (requirements.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No active requirements found
                                </td>
                            </tr>
                        `;
                    } else {
                        tbody.innerHTML = requirements.map(req => `
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    ${escapeHtml(req.requirement_name)}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">
                                        ${escapeHtml(req.requirement_type)}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    ${escapeHtml(req.description || 'No description')}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="uploadDocument(${req.requirement_id}, '${escapeHtml(req.requirement_name)}')" 
                                            class="bg-[#940505] text-white px-4 py-2 rounded-md hover:bg-white hover:text-[#940505] border border-transparent hover:border-[#940505] transition-colors">
                                        Upload Document
                                    </button>
                                </td>
                            </tr>
                        `).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading requirements:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-red-500">
                            Failed to load requirements. Please try again.
                        </td>
                    </tr>
                `;
            }
        }

        function uploadDocument(requirementId, requirementName) {
            // Create file input element
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
            
            fileInput.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    return;
                }

                // Show uploading message
                if (!confirm(`Upload "${file.name}" for ${requirementName}?`)) {
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('requirement_id', requirementId);
                formData.append('org_id', <?php echo $_SESSION['org_id']; ?>);

                try {
                    // Note: This is a placeholder. You'll need to create the document upload API endpoint
                    alert('Document upload functionality will be implemented with backend API endpoint.');
                    // const response = await fetch('/Org-Accreditation-System/backend/api/document_upload_api.php', {
                    //     method: 'POST',
                    //     body: formData
                    // });
                    
                    // const result = await response.json();
                    // if (result.status === 'success') {
                    //     alert('Document uploaded successfully!');
                    //     loadRequirements(); // Reload to show updated status
                    // } else {
                    //     alert('Upload failed: ' + result.message);
                    // }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                }
            };

            fileInput.click();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
