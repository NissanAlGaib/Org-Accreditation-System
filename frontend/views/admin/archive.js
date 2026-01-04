// Load academic years on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadAcademicYears();
    
    // If there's a selected year from URL, load archive data
    if (selectedYear) {
        await loadArchiveData(selectedYear);
    }
});

async function loadAcademicYears() {
    const container = document.getElementById('academicYearsContainer');
    
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/academic_year_api.php', {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            const years = result.data;
            
            if (years.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-gray-500">
                        No archived academic years available
                    </div>
                `;
            } else {
                container.innerHTML = years.map(year => {
                    const isSelected = selectedYear == year.academic_year_id;
                    const borderClass = isSelected ? 'border-[#940505] border-2' : 'border-gray-200';
                    
                    const semester1Start = year.semester1_start ? formatDate(year.semester1_start) : 'N/A';
                    const semester1End = year.semester1_end ? formatDate(year.semester1_end) : 'N/A';
                    const semester2Start = year.semester2_start ? formatDate(year.semester2_start) : 'N/A';
                    const semester2End = year.semester2_end ? formatDate(year.semester2_end) : 'N/A';
                    
                    return `
                        <a href="?year_id=${year.academic_year_id}" 
                           class="block p-6 bg-white border ${borderClass} rounded-lg shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-900">
                                    S.Y. ${escapeHtml(year.year_start)}-${escapeHtml(year.year_end)}
                                </h3>
                                ${isSelected ? `
                                    <svg class="w-6 h-6 text-[#940505]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                ` : ''}
                            </div>
                            <div class="text-sm text-gray-600">
                                <p class="mb-1">
                                    <span class="font-semibold">Semester 1:</span> 
                                    ${semester1Start} - ${semester1End}
                                </p>
                                <p>
                                    <span class="font-semibold">Semester 2:</span> 
                                    ${semester2Start} - ${semester2End}
                                </p>
                            </div>
                        </a>
                    `;
                }).join('');
            }
        } else {
            container.innerHTML = `
                <div class="col-span-full text-center py-8 text-red-500">
                    Error loading academic years: ${result.message || 'Unknown error'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading academic years:', error);
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-red-500">
                Failed to load academic years. Please try again.
            </div>
        `;
    }
}

async function loadArchiveData(yearId) {
    const container = document.getElementById('archiveDataContainer');
    const tbody = document.getElementById('archiveTableBody');
    
    container.classList.remove('hidden');
    container.classList.add('flex');
    
    try {
        const response = await fetch(`/Org-Accreditation-System/backend/api/academic_year_api.php?academic_year_id=${yearId}`, {
            method: 'GET'
        });
        
        if (!response.ok) throw new Error('Network error');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            const archive = result.data;
            
            if (archive.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No data available for this academic year
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = archive.map(org => {
                    const total = org.total_documents || 0;
                    const verified = org.verified_count || 0;
                    const totalRequirements = org.total_requirements || 1;
                    const completion = Math.round((verified / totalRequirements) * 100);
                    
                    const status = org.status || 'pending';
                    const statusColors = {
                        'accredited': 'bg-[#0e4b68] text-white',
                        'pending': 'bg-yellow-500 text-white',
                        'active': 'bg-blue-500 text-white',
                        'inactive': 'bg-gray-500 text-white'
                    };
                    const statusColor = statusColors[status] || 'bg-gray-500 text-white';
                    
                    return `
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    ${escapeHtml(org.org_name)}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    ${total}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    ${verified}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-green-600 h-2.5 rounded-full" style="width: ${completion}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">${completion}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="${statusColor} text-xs font-semibold px-3 py-1 rounded-full">
                                    ${capitalize(status)}
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-red-500">
                        Error loading archive data: ${result.message || 'Unknown error'}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading archive data:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-red-500">
                    Failed to load archive data. Please try again.
                </td>
            </tr>
        `;
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
