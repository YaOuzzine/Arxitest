<!-- resources/views/dashboard/test-cases/partials/notification.blade.php -->
<div x-data="notification" x-show="show" x-cloak
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-4 right-4 w-full max-w-sm p-4 rounded-lg shadow-lg pointer-events-auto"
    :class="{
        'bg-green-50 dark:bg-green-800/90 border border-green-200 dark:border-green-700': type === 'success',
        'bg-red-50 dark:bg-red-800/90 border border-red-200 dark:border-red-700': type === 'error'
    }">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i data-lucide="check-circle" class="w-6 h-6 text-green-500" x-show="type === 'success'"></i>
            <i data-lucide="alert-circle" class="w-6 h-6 text-red-500" x-show="type === 'error'"></i>
        </div>
        <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium"
                :class="{
                    'text-green-800 dark:text-green-100': type === 'success',
                    'text-red-800 dark:text-red-100': type === 'error'
                }"
                x-text="message"></p>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
            <button @click="show = false"
                class="inline-flex rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-offset-2"
                :class="{
                    'text-green-500 hover:bg-green-100 dark:hover:bg-green-700 focus:ring-green-600 dark:focus:ring-offset-green-800': type === 'success',
                    'text-red-500 hover:bg-red-100 dark:hover:bg-red-700 focus:ring-red-600 dark:focus:ring-offset-red-800': type === 'error'
                }">
                <span class="sr-only">Close</span>
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notification', () => ({
        show: false,
        message: '',
        type: 'success',
        timeout: null,

        init() {
            window.addEventListener('notify', event => {
                this.message = event.detail.message;
                this.type = event.detail.type || 'success';
                this.show = true;

                if (this.timeout) {
                    clearTimeout(this.timeout);
                }
                this.timeout = setTimeout(() => {
                    this.show = false;
                }, 5000);

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            });
        }
    }));
});
</script>
@endpush
