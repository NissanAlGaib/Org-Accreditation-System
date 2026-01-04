let currentLogo = null;
        let hasLogoFile = false;

        document.addEventListener('DOMContentLoaded', loadOrganizationData);

        async function loadOrganizationData() {
            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
                    method: 'GET'
                });

                if (!response.ok) throw new Error('Network error');
                const result = await response.json();

                if (result.status === 'success' && result.data) {
                    const organizations = result.data;
                    const userOrg = organizations.find(org => org.org_id == orgId);

                    if (userOrg) {
                        displayOrganizationData(userOrg);
                    }
                }
            } catch (error) {
                console.error('Error loading organization data:', error);
            }
        }

        function displayOrganizationData(org) {
            // Basic info
            document.getElementById('orgName').textContent = org.org_name || 'N/A';
            document.getElementById('orgId').textContent = org.org_id || 'N/A';
            
            // Status
            const status = org.status || 'pending';
            const statusColors = {
                'accredited': 'bg-green-500',
                'active': 'bg-blue-500',
                'pending': 'bg-yellow-500',
                'inactive': 'bg-gray-500'
            };
            const statusElement = document.getElementById('orgStatus');
            statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusElement.className = `inline-block ${statusColors[status] || 'bg-blue-500'} text-white text-sm font-semibold px-4 py-1.5 rounded-full`;

            // Logo
            if (org.org_logo) {
                currentLogo = org.org_logo;
                document.getElementById('orgLogoImg').src = org.org_logo;
                document.getElementById('orgLogoImg').classList.remove('hidden');
                document.getElementById('orgLogoPlaceholder').classList.add('hidden');
            }

            // Description
            if (org.org_description) {
                document.getElementById('orgDescription').textContent = org.org_description;
            }

            // President info
            const presidentName = org.first_name && org.last_name 
                ? `${org.first_name} ${org.last_name}` 
                : 'Not assigned';
            document.getElementById('presidentName').textContent = presidentName;
            document.getElementById('presidentEmail').textContent = org.email || 'N/A';

            // Dates
            const createdDate = org.created_at 
                ? new Date(org.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                : 'N/A';
            document.getElementById('createdDate').textContent = createdDate;

            const updatedDate = org.updated_at 
                ? new Date(org.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                : 'N/A';
            document.getElementById('updatedDate').textContent = updatedDate;

            // Document statistics
            document.getElementById('totalDocs').textContent = org.total_documents || 0;
            document.getElementById('verifiedDocs').textContent = org.verified_documents || 0;
            document.getElementById('pendingDocs').textContent = org.pending_documents || 0;
            document.getElementById('returnedDocs').textContent = org.returned_documents || 0;
        }

        // Modal controls
        document.getElementById('editBtn').addEventListener('click', openEditModal);
        document.getElementById('closeModal').addEventListener('click', closeEditModal);
        document.getElementById('cancelBtn').addEventListener('click', closeEditModal);

        function openEditModal() {
            // Populate current description
            const currentDesc = document.getElementById('orgDescription').textContent;
            if (currentDesc !== 'No description available yet. Click "Edit Profile" to add one.') {
                document.getElementById('descriptionInput').value = currentDesc;
            }

            // Show current logo in preview
            if (currentLogo) {
                document.getElementById('logoPreview').src = currentLogo;
                document.getElementById('logoPreview').classList.remove('hidden');
                document.getElementById('logoPlaceholderPreview').classList.add('hidden');
                document.getElementById('removeLogoBtn').classList.remove('hidden');
            }

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editForm').reset();
            hasLogoFile = false;
            
            // Reset preview
            if (currentLogo) {
                document.getElementById('logoPreview').src = currentLogo;
                document.getElementById('logoPreview').classList.remove('hidden');
                document.getElementById('logoPlaceholderPreview').classList.add('hidden');
            } else {
                document.getElementById('logoPreview').classList.add('hidden');
                document.getElementById('logoPlaceholderPreview').classList.remove('hidden');
            }
        }

        // Logo upload
        document.getElementById('uploadLogoBtn').addEventListener('click', () => {
            document.getElementById('logoInput').click();
        });

        document.getElementById('logoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                    document.getElementById('logoPreview').classList.remove('hidden');
                    document.getElementById('logoPlaceholderPreview').classList.add('hidden');
                    document.getElementById('removeLogoBtn').classList.remove('hidden');
                    hasLogoFile = true;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('removeLogoBtn').addEventListener('click', () => {
            document.getElementById('logoInput').value = '';
            document.getElementById('logoPreview').classList.add('hidden');
            document.getElementById('logoPlaceholderPreview').classList.remove('hidden');
            document.getElementById('removeLogoBtn').classList.add('hidden');
            hasLogoFile = false;
            currentLogo = null;
        });

        // Form submission
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('org_description', document.getElementById('descriptionInput').value);
            
            const logoFile = document.getElementById('logoInput').files[0];
            if (logoFile) {
                formData.append('org_logo', logoFile);
            }

            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';

            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/organization_update_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showSuccess('Organization updated successfully!');
                    closeEditModal();
                    loadOrganizationData(); // Reload data
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Failed to update organization');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save Changes';
            }
        });