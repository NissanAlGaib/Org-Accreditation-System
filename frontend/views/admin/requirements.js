const modal = document.getElementById('requirementModal');
const modalTitle = document.getElementById('modalTitle');
const form = document.getElementById('requirementForm');
const submitBtn = document.getElementById('submitBtn');

const requirementIdInput = document.getElementById('requirementId');
const requirementNameInput = document.getElementById('requirementName');
const requirementTypeInput = document.getElementById('requirementType');
const requirementDescInput = document.getElementById('requirementDesc');

let isEditMode = false;

function openModal() {
    isEditMode = false;
    modalTitle.textContent = 'Add Requirement';
    submitBtn.textContent = 'Add Requirement';
    form.reset();
    requirementIdInput.value = '';
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
}

modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeModal();
    }
});

function editRequirement(requirement) {
    isEditMode = true;
    modalTitle.textContent = 'Edit Requirement';
    submitBtn.textContent = 'Update Requirement';
    
    requirementIdInput.value = requirement.requirement_id;
    requirementNameInput.value = requirement.requirement_name;
    requirementTypeInput.value = requirement.requirement_type;
    requirementDescInput.value = requirement.description || '';
    
    modal.classList.remove('hidden');
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const originalBtnText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = isEditMode ? 'Updating...' : 'Creating...';
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const method = isEditMode ? 'PUT' : 'POST';
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(isEditMode ? 'Requirement updated successfully' : 'Requirement created successfully');
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Submission Error:', error);
        alert('An error occurred. Check console for details.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});

async function deleteRequirement(requirementId) {
    if (!confirm('Are you sure you want to delete this requirement?')) {
        return;
    }
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php?requirement_id=' + requirementId, {
            method: 'DELETE'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success') {
            alert('Requirement deleted successfully');
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Delete Error:', error);
        alert('An error occurred. Check console for details.');
    }
}
