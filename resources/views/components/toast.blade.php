<div
  x-data="{
    show: false,
    message: '',
    type: 'success',
    timeout: null,

    display(msg, t = 'success') {
      this.message = msg;
      this.type = t;
      this.show = true;
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        this.show = false;
      }, 4000);
    }
  }"
  x-init="
    @if(session('success'))
      display('{{ session('success') }}', 'success');
    @elseif(session('error'))
      display('{{ session('error') }}', 'error');
    @endif
  "
  @toast.window="display($event.detail.message, $event.detail.type || 'success')"
  class="fixed bottom-6 right-6 z-50 max-w-sm pointer-events-none"
  x-cloak
>
  <div
    x-show="show"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    :class="type === 'error' ? 'bg-red-600' : 'bg-scm-black'"
    class="pointer-events-auto px-5 py-3.5 shadow-2xl text-white flex items-center gap-3 border border-scm-gray-800"
  >
    <template x-if="type === 'success'">
      <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
      </svg>
    </template>
    <template x-if="type === 'error'">
      <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </template>
    <p class="text-xs uppercase tracking-wider font-medium" x-text="message"></p>
  </div>
</div>
