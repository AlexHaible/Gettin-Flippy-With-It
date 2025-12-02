<x-layouts.app title="Authenticate">

    <script src="[https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js](https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js)"></script>
    <script src="[https://cdn.jsdelivr.net/npm/@github/webauthn-json@2.1.1/dist/browser-ponyfill.min.js](https://cdn.jsdelivr.net/npm/@github/webauthn-json@2.1.1/dist/browser-ponyfill.min.js)"></script>

    <div class="flex flex-col items-center justify-center min-h-[70vh] text-center px-4">

        <!-- Decorative Header -->
        <div class="mb-12">
            <div class="deco-divider w-32 mx-auto mb-4"></div>
            <h1 class="font-display text-4xl text-gold-500 text-shadow-gold">Cinema Companion</h1>
            <div class="deco-divider w-32 mx-auto mt-4"></div>
        </div>

        <!-- AUTH CARD -->
        <div class="w-full max-w-sm bg-noir-800 border-2 border-gold-800 p-8 shadow-[0_0_30px_rgba(212,175,55,0.1)] relative">

            <!-- Corner Flourishes -->
            <div class="absolute top-2 left-2 w-3 h-3 border-t border-l border-gold-500"></div>
            <div class="absolute top-2 right-2 w-3 h-3 border-t border-r border-gold-500"></div>
            <div class="absolute bottom-2 left-2 w-3 h-3 border-b border-l border-gold-500"></div>
            <div class="absolute bottom-2 right-2 w-3 h-3 border-b border-r border-gold-500"></div>

            <h2 class="text-gold-200 font-display text-xl mb-6 tracking-widest uppercase">Identify Yourself</h2>

            <form id="auth-form" onsubmit="handleAuth(event)" class="flex flex-col gap-6">

                <!-- Username Input -->
                <div class="relative">
                    <input type="text" id="username" required
                           class="w-full bg-noir-900 border-b border-gold-700 text-gold-100 text-center py-3 px-4 focus:outline-none focus:border-gold-400 font-serif placeholder-gold-800 transition-colors uppercase tracking-widest"
                           placeholder="USERNAME">
                </div>

                <!-- Status Message -->
                <div id="status" class="text-xs text-gold-500 font-serif h-4"></div>

                <!-- Action Button -->
                <button type="submit" id="btn-submit"
                        class="group relative w-full py-3 bg-transparent border border-gold-600 hover:bg-gold-500 transition-colors duration-300 cursor-pointer">
                    <span class="font-display text-gold-500 tracking-[0.2em] uppercase group-hover:text-black font-bold">
                        Proceed
                    </span>
                </button>
            </form>
        </div>

    </div>

    <script>
        async function handleAuth(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const btn = document.getElementById('btn-submit');
            const status = document.getElementById('status');

            // UI Loading State
            btn.disabled = true;
            btn.classList.add('opacity-50');
            status.innerText = "Consulting the archives...";

            try {
                // STEP 1: Ask Server "Login or Register?"
                const startRes = await axios.post('{{ route("auth.start") }}', { username });
                const { flow, options } = startRes.data;

                status.innerText = flow === 'register' ? "New patron. Registering key..." : "Patron found. Verifying...";

                // STEP 2: Browser Ceremony
                let credential;
                if (flow === 'register') {
                    // Create new Passkey
                    credential = await window.webauthnJSON.create({ publicKey: options });
                } else {
                    // Get existing Passkey
                    credential = await window.webauthnJSON.get({ publicKey: options });
                }

                // STEP 3: Finish & Verify
                status.innerText = "Verifying credentials...";
                const finishRes = await axios.post('{{ route("auth.finish") }}', {
                    data: credential
                });

                status.innerText = "Access Granted.";
                window.location.href = finishRes.data.redirect || '/';

            } catch (error) {
                console.error(error);
                btn.disabled = false;
                btn.classList.remove('opacity-50');

                let msg = "Authentication failed.";
                if (error.response && error.response.data.message) {
                    msg = error.response.data.message;
                } else if (error.name === 'NotAllowedError') {
                    msg = "Operation cancelled or timed out.";
                }
                status.innerText = msg;
            }
        }
    </script>

</x-layouts.app>