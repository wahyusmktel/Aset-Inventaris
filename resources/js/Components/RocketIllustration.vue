<template>
  <div class="relative w-full h-64 md:h-72 flex items-end justify-center select-none pointer-events-none">
    <!-- Cloud Puffs Background Layers -->
    <div class="absolute -bottom-4 left-0 right-0 h-28 flex items-end justify-center">
      <!-- Back cloud layer -->
      <svg class="absolute bottom-2 w-[120%] -left-[10%] text-white/60 fill-current" viewBox="0 0 500 120" preserveAspectRatio="none">
        <path d="M0,120 Q50,40 120,70 Q190,10 270,60 Q350,20 420,70 Q480,45 500,120 Z" />
      </svg>

      <!-- Mid cloud layer -->
      <svg class="absolute bottom-0 w-[110%] -left-[5%] text-white/80 fill-current" viewBox="0 0 500 100" preserveAspectRatio="none">
        <path d="M0,100 Q40,30 100,55 Q170,0 240,45 Q310,15 380,50 Q450,25 500,100 Z" />
      </svg>

      <!-- Front cloud layer (solid white) -->
      <svg class="absolute -bottom-1 w-[120%] -left-[10%] text-white fill-current drop-shadow-sm" viewBox="0 0 500 80" preserveAspectRatio="none">
        <path d="M0,80 Q60,20 130,45 Q200,5 280,35 Q360,10 430,40 Q480,20 500,80 Z" />
      </svg>
    </div>

    <!-- Smoke Trail below rocket -->
    <div class="absolute bottom-8 w-16 h-28 bg-gradient-to-t from-white/90 via-white/50 to-transparent rounded-full blur-[3px]"></div>

    <!-- 3D Rocket SVG -->
    <div class="relative z-10 mb-6 transition-transform duration-700 ease-in-out hover:translate-y-[-8px] animate-floating">
      <svg class="w-36 h-48 md:w-40 md:h-52 filter drop-shadow-[0_15px_20px_rgba(88,34,180,0.25)]" viewBox="0 0 160 220" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <!-- Main Body Gradients -->
          <linearGradient id="rocketBody" x1="40" y1="20" x2="130" y2="170" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#9868EE" />
            <stop offset="45%" stop-color="#6F36D9" />
            <stop offset="100%" stop-color="#4F1EA8" />
          </linearGradient>
          
          <linearGradient id="rocketBodyHighlight" x1="50" y1="20" x2="90" y2="160" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#C4A8FF" stop-opacity="0.9" />
            <stop offset="60%" stop-color="#8E54F0" stop-opacity="0.1" />
            <stop offset="100%" stop-color="#551EB5" stop-opacity="0" />
          </linearGradient>

          <!-- Tip / Nosecone Gradient -->
          <linearGradient id="rocketTip" x1="70" y1="0" x2="110" y2="45" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#F3F0FA" />
            <stop offset="50%" stop-color="#E2D9F3" />
            <stop offset="100%" stop-color="#C2B2DE" />
          </linearGradient>

          <!-- Fin Left -->
          <linearGradient id="finLeft" x1="10" y1="120" x2="50" y2="180" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FFFFFF" />
            <stop offset="100%" stop-color="#D1C4E9" />
          </linearGradient>

          <!-- Fin Right -->
          <linearGradient id="finRight" x1="150" y1="120" x2="110" y2="180" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#E0D7F5" />
            <stop offset="100%" stop-color="#B39DDB" />
          </linearGradient>

          <!-- Booster Base -->
          <linearGradient id="rocketBase" x1="50" y1="155" x2="110" y2="175" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#5B21B6" />
            <stop offset="100%" stop-color="#3B0764" />
          </linearGradient>

          <!-- Window Outer -->
          <linearGradient id="windowOuter" x1="60" y1="60" x2="100" y2="100" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FFFFFF" />
            <stop offset="100%" stop-color="#D8CEEF" />
          </linearGradient>

          <!-- Window Inner Glow -->
          <radialGradient id="windowGlow" cx="70%" cy="30%" r="70%">
            <stop offset="0%" stop-color="#FFFFFF" />
            <stop offset="40%" stop-color="#80E5FF" />
            <stop offset="100%" stop-color="#0284C7" />
          </radialGradient>

          <!-- Thruster Flame / Light -->
          <linearGradient id="flameGrad" x1="80" y1="175" x2="80" y2="215" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FFFFFF" />
            <stop offset="30%" stop-color="#A78BFA" stop-opacity="0.8" />
            <stop offset="100%" stop-color="#7C3AED" stop-opacity="0" />
          </linearGradient>
        </defs>

        <!-- Thruster Glow plume -->
        <ellipse cx="80" cy="180" rx="20" ry="12" fill="#C4B5FD" opacity="0.6" />
        <path d="M68 175 L80 215 L92 175 Z" fill="url(#flameGrad)" />

        <!-- Left Fin -->
        <path d="M48 115 C45 135 32 165 18 178 C25 178 45 170 54 152 Z" fill="url(#finLeft)" filter="drop-shadow(0 4px 6px rgba(0,0,0,0.1))" />

        <!-- Right Fin -->
        <path d="M112 115 C115 135 128 165 142 178 C135 178 115 170 106 152 Z" fill="url(#finRight)" filter="drop-shadow(0 4px 6px rgba(0,0,0,0.1))" />

        <!-- Bottom Base Ring -->
        <path d="M52 160 C52 153 108 153 108 160 L102 176 C102 180 58 180 58 176 Z" fill="url(#rocketBase)" />
        <ellipse cx="80" cy="174" rx="22" ry="5" fill="#3B0764" />

        <!-- Main Body -->
        <path d="M80 8 C55 35 46 95 46 155 C58 165 102 165 114 155 C114 95 105 35 80 8 Z" fill="url(#rocketBody)" />

        <!-- Left-side lighting / 3D Specular Highlight -->
        <path d="M80 12 C62 38 52 95 52 150 C58 140 68 85 82 35 Z" fill="url(#rocketBodyHighlight)" />

        <!-- Center Fin / Keel -->
        <path d="M77 122 C77 145 74 165 68 175 C75 174 85 174 92 175 C86 165 83 145 83 122 Z" fill="#4C1D95" opacity="0.6" />

        <!-- Nose Tip White Cap -->
        <path d="M80 8 C72 20 64 36 62 48 C72 45 88 45 98 48 C96 36 88 20 80 8 Z" fill="url(#rocketTip)" />

        <!-- Porthole / Window Ring (3D look) -->
        <circle cx="80" cy="85" r="22" fill="url(#windowOuter)" filter="drop-shadow(0 3px 6px rgba(0,0,0,0.15))" />
        
        <!-- Porthole Glass -->
        <circle cx="80" cy="85" r="16" fill="url(#windowGlow)" />
        
        <!-- Porthole Reflection Shine -->
        <ellipse cx="76" cy="78" rx="6" ry="3" transform="rotate(-30 76 78)" fill="white" opacity="0.75" />
      </svg>
    </div>
  </div>
</template>

<style scoped>
@keyframes floating {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-10px) rotate(-1deg);
  }
}

.animate-floating {
  animation: floating 4s ease-in-out infinite;
}
</style>
