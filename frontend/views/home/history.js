let currentSemester = 1;
let selectedAcademicYear = null;
let academicYears = [];

document.addEventListener('DOMContentLoaded', function() {
    loadPresidentHistory();
    loadAcademicYears();
});

async function loadPresidentHistory() {
    const tbody = document.getElementById('historyTableBody');

    try {
        const response = await fetch(`/Org-Accreditation-System/backend/api/organization_api.php?previous_presidents=true&org_id=${orgId}`, {
            method: 'GET'
        });

        if (!response.ok) throw new Error('Network error');
        const result = await response.json();

        if (result.status === 'success' && result.data) {
            const previousPresidents = result.data;

            if (previousPresidents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No previous presidents found
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = previousPresidents.map(president => {
                    const createdDate = president.created_at 
                        ? new Date(president.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                        : 'N/A';

                    const statusBadge = president.status === 'archived' 
                        ? '<span class="bg-gray-500 text-white text-xs font-semibold px-3 py-1 rounded-full">Archived</span>'
                        : '<span class="bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full">Former President</span>';

                    return `
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                ${escapeHtml(president.first_name)} ${escapeHtml(president.last_name)}
                            </td>
                            <td class="px-6 py-4">
                                ${escapeHtml(president.email)}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                ${createdDate}
                            </td>
                            <td class="px-6 py-4">
                                ${statusBadge}
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        }
    } catch (error) {
        console.error('Error loading president history:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-red-500">
                    Failed to load history. Please try again.
                </td>
            </tr>
        `;
    }
}

async function loadAcademicYears() {
    const select = document.getElementById('academicYearSelect');

    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/academic_year_api.php', {
            method: 'GET'
        });

        if (!response.ok) throw new Error('Network error');
        const result = await response.json();

        if (result.status === 'success' && result.data) {
            academicYears = result.data;
            
            // Filter out the current active year to show only historical data
            const historicalYears = academicYears.filter(year => year.is_active != 1);

            if (historicalYears.length === 0) {
                select.innerHTML = '<option value="">No historical data available</option>';
            } else {
                select.innerHTML = '<option value="">Select an academic year</option>' +
                    historicalYears.map(year => 
                        `<option value="${year.academic_year_id}">${year.year_start} - ${year.year_end}</option>`
                    ).join('');
            }
        }
    } catch (error) {
        console.error('Error loading academic years:', error);
        select.innerHTML = '<option value="">Error loading years</option>';
    }
}

async function loadAccreditationRecords() {
    const selectElement = document.getElementById('academicYearSelect');
    const yearId = selectElement.value;
    
    if (!yearId) {
        document.getElementById('recordsTableBody').innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    Please select an academic year to view records
                </td>
            </tr>
        `;
        document.getElementById('semesterTabs').classList.add('hidden');
        return;
    }

    selectedAcademicYear = academicYears.find(year => year.academic_year_id == yearId);
    document.getElementById('semesterTabs').classList.remove('hidden');
    currentSemester = 1;
    switchSemester(1);
}

async function switchSemester(semester) {
    currentSemester = semester;
    
    // Update tab styling
    document.getElementById('semester1Tab').className = semester === 1 
        ? 'inline-block p-4 border-b-2 border-[#940505] text-[#940505] rounded-t-lg'
        : 'inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[#940505] hover:border-[#940505]';
    document.getElementById('semester2Tab').className = semester === 2 
        ? 'inline-block p-4 border-b-2 border-[#940505] text-[#940505] rounded-t-lg'
        : 'inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[#940505] hover:border-[#940505]';

    const tbody = document.getElementById('recordsTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                Loading records...
            </td>
        </tr>
    `;

    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php', {
            method: 'GET'
        });

        if (!response.ok) throw new Error('Network error');
        const result = await response.json();

        if (result.status === 'success' && result.data) {
            // Filter documents for current org and selected academic year/semester
            const orgDocuments = result.data.filter(doc => 
                doc.org_id == orgId &&
                doc.academic_year_id == selectedAcademicYear.academic_year_id
            );

            // Further filter by semester dates
            const semesterDocs = orgDocuments.filter(doc => {
                const submittedDate = new Date(doc.submitted_at);
                const semStart = new Date(semester === 1 ? selectedAcademicYear.semester1_start : selectedAcademicYear.semester2_start);
                const semEnd = new Date(semester === 1 ? selectedAcademicYear.semester1_end : selectedAcademicYear.semester2_end);
                return submittedDate >= semStart && submittedDate <= semEnd;
            });

            if (semesterDocs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No documents submitted during Semester ${semester}
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = semesterDocs.map(doc => {
                    const submittedDate = new Date(doc.submitted_at).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });

                    const statusColors = {
                        'verified': 'bg-green-500',
                        'pending': 'bg-yellow-500',
                        'returned': 'bg-red-500'
                    };

                    return `
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                ${escapeHtml(doc.requirement_name || 'N/A')}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">
                                    ${escapeHtml(doc.requirement_type || 'N/A')}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                ${escapeHtml(doc.file_name || 'N/A')}
                            </td>
                            <td class="px-6 py-4">
                                <span class="${statusColors[doc.status] || 'bg-gray-500'} text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    ${escapeHtml(doc.status ? doc.status.charAt(0).toUpperCase() + doc.status.slice(1) : 'N/A')}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                ${submittedDate}
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
        console.error('Error loading accreditation records:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    Failed to load records. Please try again.
                </td>
            </tr>
        `;
    }
}

function viewDocument(documentId) {
    if (documentId) {
        window.open(`/Org-Accreditation-System/frontend/views/common/view-document.php?id=${documentId}`, '_blank', 'width=1200,height=800');
    } else {
        showInfo('Document not available');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
