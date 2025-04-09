export default function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.classList.add(
        'fixed', 'bottom-4', 'left-4', 'px-4', 'py-3', 'rounded-md', 'shadow-md',
        'transform', 'transition-transform', 'duration-300', 'translate-y-0', 'max-w-xs'
    );

    if (type === 'success') {
        notification.classList.add('bg-green-50', 'dark:bg-green-900/20', 'text-green-600', 'dark:text-green-400');
    } else {
        notification.classList.add('bg-red-50', 'dark:bg-red-900/20', 'text-red-600', 'dark:text-red-400');
    }


    notification.innerHTML = message;
    document.body.appendChild(notification);

    // Auto-remove animation
    setTimeout(() => {
        notification.classList.add('translate-y-[100%]', 'opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
