<template>
    <div>
        <h2>
            Two Factor Authentication
        </h2>

        <div v-if="qrCode" v-html="qrCode" />

        <confirm-password v-if="!twoFactorEnabled" @confirmed="enableTwoFactorAuthentication()">
            <button>
                Enable
            </button>
        </confirm-password>

        <confirm-password v-else @confirmed="disableTwoFactorAuthentication()">
            <button>
                Disable
            </button>
        </confirm-password>
    </div>
</template>

<script>
import ConfirmPassword from './ConfirmPassword.vue'

export default {
    components: {
        ConfirmPassword
    },
    props: {
        enabled: {
            type: Boolean,
            default: false
        }
    },
    data () {
        return {
            twoFactorEnabled: this.enabled,
            qrCode: ''
        }
    },

    methods: {
        enableTwoFactorAuthentication () {
            axios.post('/pos/user/two-factor-authentication')
                .then(() => {
                    return Promise.all([
                        this.showQrCode()
                    ])
                }).then(() => {
                    this.twoFactorEnabled = true
                })
        },

        showQrCode () {
            return axios.get('/pos/user/two-factor-qr-code')
                .then(response => {
                    this.qrCode = response.data.svg
                })
        },

        disableTwoFactorAuthentication () {
            axios.delete('/pos/user/two-factor-authentication')
                .then(() => {
                    this.twoFactorEnabled = false
                    this.qrCode = ''
                })
        }
    }
}
</script>