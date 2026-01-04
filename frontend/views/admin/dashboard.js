// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadDashboardData();
    await loadRecentSubmissions();
});

async function loadDashboardData() {
    try {
        // Fetch organizations data
        const orgResponse = await fetch('/Org-Accreditation-System/backend/api/organization_api.php');
        const orgResult = await orgResponse.json();
        
        // Fetch requirements data
        const reqResponse = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php');
        const reqResult = await reqResponse.json();
        
        if (orgResult.status === 'success' && reqResult.status === 'success') {
            const organizations = orgResult.data || [];
            const requirements = reqResult.data || [];
            const totalRequirements = requirements.filter(r => r.is_active == 1).length;
            
            // Calculate stats
            let totalOrgs = organizations.length;
            let fullyAccredited = 0;
            let needsAttention = 0;
            let totalPending = 0;
            
            organizations.forEach(org => {
                const verified = parseInt(org.verified_documents) || 0;
                const pending = parseInt(org.pending_documents) || 0;
                const returned = parseInt(org.returned_documents) || 0;
                
                totalPending += pending;
                
                if (verified >= totalRequirements && totalRequirements > 0) {
                    fullyAccredited++;
                }
                
                if (returned > 0) {
                    needsAttention++;
                }
            });
            
            const completionRate = totalOrgs > 0 ? Math.round((fullyAccredited / totalOrgs) * 100) : 0;
            
            // Update dashboard cards
            document.getElementById('totalOrgs').textContent = totalOrgs;
            document.getElementById('pendingReqs').textContent = totalPending;
            document.getElementById('fullyAccredited').textContent = fullyAccredited;
            document.getElementById('fullyAccreditedRate').textContent = `${completionRate}% completion rate`;
            document.getElementById('needsAttention').textContent = needsAttention;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadRecentSubmissions() {
    try {
        const response = await fetch('/Org-Accreditation-System/backend/api/document_api.php?recent=true&limit=5');
        const result = await response.json();
        
        if (result.status === 'success') {
            const submissions = result.data || [];
            const container = document.getElementById('recentSubmissionsContainer');
            
            if (submissions.length === 0) {
                container.innerHTML = '<div class="p-5 text-center text-gray-500">No recent submissions</div>';
            } else {
                container.innerHTML = submissions.map(sub => {
                    const timeAgo = getTimeAgo(new Date(sub.submitted_at));
                    return `
                        <div class="border border-gray-400 w-full rounded-2xl mb-3">
                            <div class="p-5 px-8 flex flex-col md:flex-row justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <p class="dm-sans-bold text-xl truncate">${escapeHtml(sub.org_name)}</p>
                                    <p class="text-md truncate">${escapeHtml(sub.requirement_name)}</p>
                                    <p class="text-sm text-gray-500">${timeAgo}</p>
                                </div>
                                <div class="flex justify-center items-center flex-shrink-0">
                                    <a href="/Org-Accreditation-System/frontend/views/admin/review-documents.php?org_id=${sub.org_id}" 
                                       class="bg-[#940505] text-white hover:text-[#940505] px-8 py-2 rounded-lg hover:bg-white border hover:border-black ease-in-out duration-300 whitespace-nowrap">
                                       Review
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }
    } catch (error) {
        console.error('Error loading recent submissions:', error);
        document.getElementById('recentSubmissionsContainer').innerHTML = 
            '<div class="p-5 text-center text-red-500">Error loading recent submissions</div>';
    }
}

function getTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
