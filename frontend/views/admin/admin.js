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

    function openModal() {
        modal.classList.remove('hidden');
        resetToDefaultState();
    }

    function closeModal() {
        modal.classList.add('hidden');

        if (!successState.classList.contains('hidden')) {
            window.location.reload();
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
            const response = await fetch('../../backend/api/user_api.php', {
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