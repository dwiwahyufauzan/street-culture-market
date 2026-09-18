{{-- Scrolling marquee ticker — visual editorial statement --}}
<div class="overflow-hidden bg-scm-black text-white py-4 select-none border-y border-scm-gray-900">
  <div class="marquee-track whitespace-nowrap">
    {{-- Repeat set twice so loop is seamless --}}
    @foreach([1, 2] as $_)
      <span class="inline-flex items-center gap-8 pr-8">
        <span class="text-xs uppercase tracking-[0.35em] font-semibold">Street Culture Market</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
        <span class="text-xs uppercase tracking-[0.35em] font-light text-scm-gray-400">Limited Production Capsules</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
        <span class="text-xs uppercase tracking-[0.35em] font-semibold">Heavyweight Textiles</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
        <span class="text-xs uppercase tracking-[0.35em] font-light text-scm-gray-400">Curated Underground Apparel</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
        <span class="text-xs uppercase tracking-[0.35em] font-semibold">Jakarta — Worldwide Shipping</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
        <span class="text-xs uppercase tracking-[0.35em] font-light text-scm-gray-400">Autumn / Winter Collection Active</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
        <span class="text-xs uppercase tracking-[0.35em] font-semibold">Free Domestic Delivery Rp 500k+</span>
        <span class="w-1 h-1 bg-scm-gray-600 rounded-full inline-block"></span>
      </span>
    @endforeach
  </div>
</div>
