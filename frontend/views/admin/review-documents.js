const returnModal = document.getElementById('returnModal');
const returnForm = document.getElementById('returnForm');
const documentIdInput = document.getElementById('documentIdInput');
const remarksInput = document.getElementById('remarksInput');

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
            window.location.reload();
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
