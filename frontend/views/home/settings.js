const orgId = orgId;
        const userId = userId;

        // Load data on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await loadUserData();
            await loadOrganizationData();
            await loadActiveAcademicYear();
        });

        async function loadUserData() {
            try {
                const response = await fetch(`/Org-Accreditation-System/backend/api/user_api.php?user_id=${userId}`);
                const result = await response.json();
                
                if (result.status === 'success' && result.data) {
                    document.getElementById('email').value = result.data.email || '';
                }
            } catch (error) {
                console.error('Error loading user data:', error);
            }
        }

        async function loadOrganizationData() {
            try {
                const response = await fetch(`/Org-Accreditation-System/backend/api/organization_api.php?org_id=${orgId}`);
                const result = await response.json();
                
                if (result.status === 'success' && result.data) {
                    const org = result.data;
                    document.getElementById('orgName').value = org.org_name || '';
                    
                    const status = org.status || 'pending';
                    const statusColors = {
                        'accredited': 'bg-green-500',
                        'active': 'bg-blue-500',
                        'pending': 'bg-yellow-500',
                        'inactive': 'bg-gray-500'
                    };
                    const statusElement = document.getElementById('orgStatusBadge');
                    statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusElement.className = `inline-block ${statusColors[status] || 'bg-blue-500'} text-white text-sm font-semibold px-4 py-2 rounded-full`;
                }
            } catch (error) {
                console.error('Error loading organization data:', error);
            }
        }

        async function loadActiveAcademicYear() {
            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/academic_year_api.php?active=1');
                const result = await response.json();
                
                if (result.status === 'success' && result.data) {
                    const year = result.data;
                    const yearText = `${year.year_start}-${year.year_end}`;
                    document.getElementById('academicYear').textContent = yearText;
                    
                    // Load compliance score
                    await loadComplianceScore(year.academic_year_id);
                }
            } catch (error) {
                console.error('Error loading academic year:', error);
            }
        }

        async function loadComplianceScore(academicYearId) {
            try {
                const response = await fetch(`/Org-Accreditation-System/backend/api/organization_api.php?compliance_score=1&org_id=${orgId}&academic_year_id=${academicYearId}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    document.getElementById('complianceScore').textContent = `${result.compliance_score.toFixed(1)}%`;
                }
                
                // Load accreditation status
                const statusResponse = await fetch(`/Org-Accreditation-System/backend/api/organization_api.php?accreditation_status=1&org_id=${orgId}&academic_year_id=${academicYearId}`);
                const statusResult = await statusResponse.json();
                
                if (statusResult.status === 'success') {
                    document.getElementById('accreditationStatus').textContent = statusResult.accreditation_status;
                }
            } catch (error) {
                console.error('Error loading compliance score:', error);
            }
        }

        // Edit Profile
        document.getElementById('editProfileBtn').addEventListener('click', () => {
            document.getElementById('firstName').readOnly = false;
            document.getElementById('lastName').readOnly = false;
            document.getElementById('email').readOnly = false;
            document.getElementById('firstName').classList.remove('bg-gray-50');
            document.getElementById('lastName').classList.remove('bg-gray-50');
            document.getElementById('email').classList.remove('bg-gray-50');
            document.getElementById('editButtons').classList.remove('hidden');
            document.getElementById('editProfileBtn').classList.add('hidden');
        });

        document.getElementById('cancelEditBtn').addEventListener('click', () => {
            document.getElementById('firstName').readOnly = true;
            document.getElementById('lastName').readOnly = true;
            document.getElementById('email').readOnly = true;
            document.getElementById('firstName').classList.add('bg-gray-50');
            document.getElementById('lastName').classList.add('bg-gray-50');
            document.getElementById('email').classList.add('bg-gray-50');
            document.getElementById('editButtons').classList.add('hidden');
            document.getElementById('editProfileBtn').classList.remove('hidden');
            loadUserData();
        });

        document.getElementById('saveProfileBtn').addEventListener('click', async () => {
            showInfo('Profile update functionality will be implemented.');
        });

        // Change Password
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                showWarning('Please fill in all fields');
                return;
            }
            
            if (newPassword.length < 8) {
                showWarning('Password must be at least 8 characters long');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showWarning('New passwords do not match');
                return;
            }
            
            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/user_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'change_password',
                        new_password: newPassword
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showSuccess('Password changed successfully');
                    document.getElementById('passwordForm').reset();
                    if (result.redirect) {
                        setTimeout(() => window.location.href = result.redirect, 1500);
                    }
                } else {
                    showError(result.message || 'Failed to change password');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while changing password');
            }
        });