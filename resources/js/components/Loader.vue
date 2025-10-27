<template>
    <div class="fixed inset-0 z-[100] bg-red-700">
        <div class="absolute top-1/2 w-full -translate-y-1/2 text-center text-white">
            <div class="flex items-center justify-center gap-3">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: white;
    opacity: 0;
    transform: scale(0.5);
    animation: pulse 1.5s infinite ease-in-out;
}

.dot:nth-child(2) {
    animation-delay: 0.1s;
}
.dot:nth-child(3) {
    animation-delay: 0.2s;
}
.dot:nth-child(4) {
    animation-delay: 0.3s;
}
.dot:nth-child(5) {
    animation-delay: 0.4s;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.2;
        transform: scale(0.5);
    }
    50% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
