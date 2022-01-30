<template>
    <div class="font-sans text-gray-900 antialiased">
        <div class="bg-gray-100 pt-4">
            <div class="flex min-h-screen flex-col items-center pt-6 sm:pt-0">
                <div>
                    <jet-authentication-card-logo />
                </div>

                <div
                    v-html="terms"
                    class="prose mt-6 w-full overflow-hidden bg-white p-6 shadow-md sm:max-w-2xl sm:rounded-lg"
                ></div>
            </div>
        </div>
    </div>
</template>

<script>
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo';

export default {
    props: ['terms'],

    components: {
        JetAuthenticationCardLogo,
    },
};
</script>
