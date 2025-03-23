import './bootstrap';
import './test-execution';
import * as lucide from 'lucide';

// Initialize Lucide icons when the page loads
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons({
        icons: {
            Search: lucide.Search,
            HelpCircle: lucide.HelpCircle,
            Plus: lucide.Plus,
            Home: lucide.Home,
            Inbox: lucide.Inbox,
            BarChart2: lucide.BarChart2,
            FileText: lucide.FileText,
            Terminal: lucide.Terminal,
            Users: lucide.Users,
            Files: lucide.Files,
            Plug2: lucide.Plug2,
            Palette: lucide.Palette,
            Edit3: lucide.Edit3,
            Filter: lucide.Filter,
            MoreHorizontal: lucide.MoreHorizontal
        }
    });
});

document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.custom-dropdown');
    dropdowns.forEach(function(dropdown) {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        if (trigger.contains(event.target)) {
            // Toggle menu visibility on trigger click
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        } else {
            // Hide menu when clicking outside
            menu.style.display = 'none';
        }
    });
});
