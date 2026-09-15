<footer class="bg-scm-black text-white mt-24 border-t border-scm-gray-800">
  <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-16 lg:py-20">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">

      <!-- Column 1: Brand & Philosophy -->
      <div class="space-y-4">
        <span class="text-2xl font-black uppercase tracking-[0.25em] text-white">
          SCM
        </span>
        <p class="text-xs uppercase tracking-widest text-scm-gray-400">Street Culture Market</p>
        <p class="text-sm text-scm-gray-400 leading-relaxed font-light">
          Independent streetwear and urban apparel curated with focus on heavyweight silhouettes, bespoke textiles, and contemporary youth counter-culture.
        </p>
        <div class="pt-2 text-xs text-scm-gray-500 uppercase tracking-widest">
          Jakarta, Indonesia — Worldwide Shipping
        </div>
      </div>

      <!-- Column 2: Navigation / Shop -->
      <div class="space-y-4">
        <h4 class="text-xs uppercase tracking-[0.25em] text-scm-gray-300 font-semibold border-b border-scm-gray-800 pb-2">
          Collections
        </h4>
        <ul class="space-y-2.5 text-sm">
          <li>
            <a href="{{ route('products.index') }}" class="text-scm-gray-400 hover:text-white transition-colors">
              All Products
            </a>
          </li>
          <li>
            <a href="{{ route('products.index', ['category' => 't-shirts-tops']) }}" class="text-scm-gray-400 hover:text-white transition-colors">
              T-Shirts & Tops
            </a>
          </li>
          <li>
            <a href="{{ route('products.index', ['category' => 'hoodies-sweats']) }}" class="text-scm-gray-400 hover:text-white transition-colors">
              Hoodies & Sweats
            </a>
          </li>
          <li>
            <a href="{{ route('products.index', ['category' => 'jackets-outerwear']) }}" class="text-scm-gray-400 hover:text-white transition-colors">
              Tactical Outerwear
            </a>
          </li>
          <li>
            <a href="{{ route('products.index', ['category' => 'pants-cargo']) }}" class="text-scm-gray-400 hover:text-white transition-colors">
              Cargo & Pants
            </a>
          </li>
        </ul>
      </div>

      <!-- Column 3: Customer Care -->
      <div class="space-y-4">
        <h4 class="text-xs uppercase tracking-[0.25em] text-scm-gray-300 font-semibold border-b border-scm-gray-800 pb-2">
          Assistance
        </h4>
        <ul class="space-y-2.5 text-sm">
          <li>
            <span class="text-scm-gray-400">Domestic Delivery: JNE / Sicepat</span>
          </li>
          <li>
            <span class="text-scm-gray-400">Estimated Arrival: 1 - 3 Days</span>
          </li>
          <li>
            <span class="text-scm-gray-400">Payment: Midtrans VA, QRIS & E-Wallet</span>
          </li>
          <li>
            <span class="text-scm-gray-400">Support: care@streetculturemarket.com</span>
          </li>
        </ul>
      </div>

      <!-- Column 4: Newsletter -->
      <div class="space-y-4">
        <h4 class="text-xs uppercase tracking-[0.25em] text-scm-gray-300 font-semibold border-b border-scm-gray-800 pb-2">
          Dispatch Bulletin
        </h4>
        <p class="text-sm text-scm-gray-400 leading-relaxed font-light">
          Subscribe to receive secret drop notifications and private capsule releases.
        </p>
        <form onsubmit="event.preventDefault(); alert('Subscribed to drop notification!');" class="space-y-2">
          <div class="flex">
            <input
              type="email"
              required
              placeholder="ENTER EMAIL"
              class="w-full bg-scm-gray-900 border border-scm-gray-700 text-white placeholder-scm-gray-500 text-xs px-3 py-3 uppercase tracking-wider focus:outline-none focus:border-white"
            >
            <button
              type="submit"
              class="bg-white text-scm-black px-5 py-3 text-xs uppercase tracking-widest font-bold hover:bg-scm-gray-200 transition-colors"
            >
              Join
            </button>
          </div>
        </form>
      </div>

    </div>

    <!-- Bottom Line -->
    <div class="mt-16 pt-8 border-t border-scm-gray-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-scm-gray-500 uppercase tracking-widest">
      <div>
        &copy; {{ date('Y') }} Street Culture Market. Curated Underground Apparel.
      </div>
      <div class="flex items-center gap-6">
        <span>Privacy Policy</span>
        <span>Terms of Service</span>
        <span>Indonesia</span>
      </div>
    </div>
  </div>
</footer>
