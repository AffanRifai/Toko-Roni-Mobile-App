{{-- resources/views/components/toast.blade.php --}}
<div x-data="{ 
    messages: [],
    remove(id) {
        this.messages = this.messages.filter(m => m.id !== id);
    },
    add(message, type = 'success') {
        const id = Date.now();
        this.messages.push({ id, message, type });
        setTimeout(() => this.remove(id), 5000);
    }
}"
@notify.window="add($event.detail.message, $event.detail.type)"
class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 max-w-sm w-full">
    
    <template x-for="msg in messages" :key="msg.id">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="p-4 rounded-xl shadow-2xl border flex items-center gap-3 backdrop-blur-md"
             :class="{
                'bg-green-500/90 text-white border-green-600': msg.type === 'success',
                'bg-red-500/90 text-white border-red-600': msg.type === 'error',
                'bg-indigo-500/90 text-white border-indigo-600': msg.type === 'info',
                'bg-yellow-500/90 text-white border-yellow-600': msg.type === 'warning'
             }">
            
            <div class="flex-shrink-0">
                <i class="fas" :class="{
                    'fa-check-circle': msg.type === 'success',
                    'fa-exclamation-circle': msg.type === 'error',
                    'fa-info-circle': msg.type === 'info',
                    'fa-exclamation-triangle': msg.type === 'warning'
                }"></i>
            </div>
            
            <p class="text-sm font-medium flex-1" x-text="msg.message"></p>
            
            <button @click="remove(msg.id)" class="text-white/70 hover:text-white">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    </template>
</div>
