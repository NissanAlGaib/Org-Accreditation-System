    const modal = document.getElementById('createAccountModal');
    const formState = document.getElementById('modalFormState');
    const successState = document.getElementById('modalSuccessState');
    const form = document.getElementById('createAccountForm');
    
    const toggleBtn = document.getElementById('toggleOrgModeBtn');
    const selectContainer = document.getElementById('orgSelectContainer');
    const newContainer = document.getElementById('orgNewContainer');
    const orgSelect = document.getElementById('orgIdInput');
    const orgInput = document.getElementById('newOrgInput');
    const submitBtn = document.getElementById('submitBtn');

    let isNewOrgMode = false;

    // Load organizations on page load
    loadOrganizations();
    loadOrganizationsTable();

    async function loadOrganizations() {
        try {
            const response = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
                method: 'GET'
            });
            
            if (!response.ok) throw new Error('Network error');
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                orgSelect.innerHTML = '<option value="">Select an Organization...</option>';
                result.data.forEach(org => {
                    const option = document.createElement('option');
                    option.value = org.org_id;
                    option.textContent = org.org_name;
                    orgSelect.appendChild(option);
                });
            } else {
                orgSelect.innerHTML = '<option value="">Failed to load organizations</option>';
            }
        } catch (error) {
            console.error('Error loading organizations:', error);
            orgSelect.innerHTML = '<option value="">Error loading organizations</option>';
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        resetToDefaultState();
    }

    function closeModal() {
        modal.classList.add('hidden');

        if (!successState.classList.contains('hidden')) {
            // Reload the organizations table and dropdown
            loadOrganizationsTable();
            loadOrganizations();
        }
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    function resetToDefaultState() {
        formState.classList.remove('hidden');
        successState.classList.add('hidden');
        form.reset();

        isNewOrgMode = false;
        selectContainer.classList.remove('hidden');
        newContainer.classList.add('hidden');
        toggleBtn.textContent = "Register New Organization?";
        
        orgInput.removeAttribute('required');
        orgSelect.setAttribute('required', 'true');
        orgInput.value = "";
    }

    toggleBtn.addEventListener('click', () => {
        isNewOrgMode = !isNewOrgMode;

        if (isNewOrgMode) {
            selectContainer.classList.add('hidden');
            newContainer.classList.remove('hidden');
            toggleBtn.textContent = "Select Existing Organization?";
            
            orgSelect.removeAttribute('required');
            orgInput.setAttribute('required', 'true');
            orgSelect.value = "";
            
        } else {
            selectContainer.classList.remove('hidden');
            newContainer.classList.add('hidden');
            toggleBtn.textContent = "Register New Organization?";
            
            orgInput.removeAttribute('required');
            orgSelect.setAttribute('required', 'true');
            orgInput.value = "";
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const originalBtnText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = "Creating...";

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (isNewOrgMode) {
            data.action = "create_new_org_and_president";
        } else {
            data.action = "create_org_president";
        }

        try {
            const response = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('Network error');
            const result = await response.json();

            if (result.status === 'success') {
                formState.classList.add('hidden');
                successState.classList.remove('hidden');
                
                document.getElementById('tempPasswordDisplay').innerText = result.temp_password;
                
                // Reload the organizations table
                await loadOrganizationsTable();
            } else {
                alert("Error: " + result.message);
            }

        } catch (error) {
            console.error("Submission Error:", error);
            alert("An error occurred. Check console for details.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = originalBtnText;
        }
    });

    function copyPassword() {
        const passText = document.getElementById('tempPasswordDisplay').innerText;
        
        navigator.clipboard.writeText(passText).then(() => {
            alert("Password copied to clipboard!");
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }

    async function loadOrganizationsTable() {
        const tbody = document.getElementById('organizationsTableBody');
        
        if (!tbody) return; // Return if table doesn't exist on this page
        
        try {
            const response = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
                method: 'GET'
            });
            
            if (!response.ok) throw new Error('Network error');
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                const organizations = result.data;
                
                if (organizations.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No organizations registered yet
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = organizations.map(org => {
                        const statusColors = {
                            'active': 'bg-[#0e4b68] text-white',
                            'archived': 'bg-gray-500 text-white',
                            'pending': 'bg-yellow-500 text-white'
                        };
                        const status = org.status || 'pending';
                        const statusColor = statusColors[status] || 'bg-gray-500 text-white';
                        
                        const createdDate = org.created_at ? new Date(org.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
                        const presidentName = org.first_name ? `${org.first_name} ${org.last_name}` : 'Not assigned';
                        const email = org.email || '-';
                        const tempPassword = org.temp_password || '-';
                        
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
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span class="text-gray-700">${escapeHtml(presidentName)}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-gray-500">${escapeHtml(email)}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 whitespace-nowrap">${createdDate}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded text-xs font-mono border border-gray-200">
                                            ${escapeHtml(tempPassword)}
                                        </span>
                                        ${tempPassword !== '-' ? `
                                            <button onclick="copyPasswordToClipboard('${tempPassword}')" class="text-gray-400 hover:text-gray-600 transition-colors" title="Copy">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        ` : ''}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="${statusColor} text-xs font-semibold px-3 py-1 rounded-full">
                                        ${capitalize(status)}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-3">
                                        <button class="text-gray-600 hover:text-blue-600 transition-colors" title="View details">
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
                            Error loading organizations: ${result.message || 'Unknown error'}
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading organizations table:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-red-500">
                        Failed to load organizations. Please try again.
                    </td>
                </tr>
            `;
        }
    }

    function copyPasswordToClipboard(password) {
        navigator.clipboard.writeText(password).then(() => {
            alert("Password copied to clipboard!");
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            alert("Failed to copy password");
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }