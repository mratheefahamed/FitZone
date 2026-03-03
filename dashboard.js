document.addEventListener('DOMContentLoaded', () => {
    // Get all sidebar links
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    
    // Get all sections
    const sections = document.querySelectorAll('.section');
    
    // Add click event to each sidebar link
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href').startsWith('#')) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                link.classList.add('active');
                
                // Hide all sections
                sections.forEach(section => section.style.display = 'none');
                
                // Show the target section
                const targetId = link.getAttribute('href').substring(1);
                document.getElementById(targetId).style.display = 'block';
            });
        }
    });
    
    // Show first section by default
    if (sections.length > 0) {
        sections.forEach(section => section.style.display = 'none');
        sections[0].style.display = 'block';
    }
});
