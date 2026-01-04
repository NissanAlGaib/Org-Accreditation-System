document.addEventListener('DOMContentLoaded', loadDashboardData);

async function loadDashboardData() {
    try {
        // Fetch organization data
        const orgResponse = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
            method: 'GET'
        });

        if (!orgResponse.ok) throw new Error('Network error');
        const orgResult = await orgResponse.json();

        // Fetch requirements data to get total count
        const reqResponse = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
            method: 'GET'
        });

        let totalRequirements = 0;
        if (reqResponse.ok) {
            const reqResult = await reqResponse.json();
            if (reqResult.status === 'success' && reqResult.data) {
                // Count only active requirements
                totalRequirements = reqResult.data.filter(req => req.is_active == 1).length;
            }
        }

        if (orgResult.status === 'success' && orgResult.data) {
            // Find the current user's organization
            const organizations = orgResult.data;
            const userOrg = organizations.find(org => org.org_id == orgId);

            if (userOrg) {
                const total = parseInt(userOrg.total_documents) || 0;
                const verified = parseInt(userOrg.verified_documents) || 0;
                const pending = parseInt(userOrg.pending_documents) || 0;
                const returned = parseInt(userOrg.returned_documents) || 0;

                // Calculate completion based on verified documents out of total required documents
                const completion = totalRequirements > 0 ? Math.round((verified / totalRequirements) * 100) : 0;

                document.getElementById('totalDocuments').textContent = total;
                document.getElementById('verifiedDocuments').textContent = verified;
                document.getElementById('pendingDocuments').textContent = pending;
                document.getElementById('returnedDocuments').textContent = returned;
                document.getElementById('progressBar').style.width = completion + '%';
                document.getElementById('progressPercentage').textContent = completion + '%';

                const status = userOrg.status || 'pending';
                const statusColors = {
                    'accredited': 'bg-green-500',
                    'active': 'bg-blue-500',
                    'pending': 'bg-yellow-500',
                    'inactive': 'bg-gray-500'
                };
                const statusElement = document.getElementById('orgStatus');
                statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusElement.className = `${statusColors[status] || 'bg-blue-500'} text-white text-sm font-semibold px-4 py-2 rounded-full`;
            }
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}
