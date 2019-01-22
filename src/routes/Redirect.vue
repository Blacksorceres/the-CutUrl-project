<template>
    <v-content>
        <v-container fluid fill-height>
            <v-layout align-center justify-center>
                <v-flex xs12 sm10 md6>
                    <v-card class="elevation-12" dark>
                        <v-card-text class="text-xs-center">
                            <v-layout row wrap>
                                <v-flex
                                    xs12
                                    justify-center
                                    align-center
                                    class="px-25"
                                    v-if="this.showSection == 'countdown'"
                                >
                                    <p style="margin:0;">
                                        Sending you to the link in {{ seconds }}s...
                                        <br>
                                        {{ seconds==0.0?'Redirecting now...':''}}
                                    </p>
                                </v-flex>
                                <v-flex
                                    xs12
                                    justify-center
                                    align-center
                                    class="px-25"
                                    v-if="this.showSection == 'password'"
                                >
                                    <v-text-field
                                        v-model="pwd.pwd"
                                        prepend-icon="mdi-lock-question"
                                        :append-icon="pwd.showPwd ? 'mdi-eye' : 'mdi-eye-off'"
                                        :type="pwd.showPwd ? 'text' : 'password'"
                                        :error="pwd.okay === false"
                                        :success="pwd.okay === true"
                                        hint="Type in the link password to get redirected"
                                        label="Password"
                                        @click:append="pwd.showPwd = !pwd.showPwd"
                                    ></v-text-field>
                                    <v-btn
                                        outline
                                        color="green"
                                        @click="loadLinkData()"
                                    >
                                        Go
                                        <v-icon right dark>mdi-arrow-right</v-icon>
                                    </v-btn>
                                </v-flex>
                                <v-flex
                                    xs12
                                    justify-center
                                    align-center
                                    class="px-25"
                                    v-if="this.showSection == 'error'"
                                >
                                    <p style="margin:0;">An error occured!<br>{{error.msg}}<br><v-btn outline color="blue" href="https://cuturl.it/">Back home</v-btn></p>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
    </v-content>
</template>

<script>
import store from './../store.js';
import axios from 'axios';

export default {
    data () {
        return {
            store: store,
            timer: null,
            msRemaining: null,
            delay: 5000,
            loading: false,
            showSection: 'loading',
            link: {
                checked: false,
                exists: null,
                hasPwd: false,
                hasAds:  false,
                expires: null,
                redirect: null,
                short: this.$route.params.short
            },
            pwd: {
                pwd: null,
                okay: null,
                showPwd: false
            },
            error: {
                msg: null
            }
        }
    },
	computed: {
		seconds () {
			return Math.floor( ( this.msRemaining / 1000 ) % 60 );
    },
    getDomain: () => {
        return window.location.hostname;
    }
	},
	created () {
		this.loadLinkData();
	},
	methods: {
		loadLinkData () {
			this.loading = true;
			axios.post('https://cuturl.it/api/send_getData.php', {
          short: this.link.short,
          domain: this.getDomain,
				pwd: this.pwd.pwd
			}).then(response => {
				/*
				 If we get the data from the API
			 	*/
				let dat = response.data;
				this.loading = false;
				/*
				 Check if the link was checked on the backend
				*/
				if (dat.data.checked == true){
					this.link.checked = true;
					/*
					 If the link exists in the database
					*/
					if (dat.data.exists == true){
						this.link.exists = true;
						this.pwd.okay = dat.data.pwdOkay;

						if (dat.success == true){
							this.link.redirect = dat.data.original;
							/*
							START THE COUNTDOWN
							*/
							this.showSection = 'countdown';
							this.msRemaining = dat.instant == true ? 0 : this.delay;
							this.timer = setInterval(() => this.countdown(), 100);
						} else {
							if (dat.data.hasPwd == true) {
								this.showSection = 'password';
							} else {
								this.showSection = 'error';
								this.error.msg = 'An unknown error occured!';
							}
						}

					} else {
						/*
						 If the link does not exist in our database
						*/
						this.link.exists = false;
						this.showSection = 'error';
						this.error.msg = 'The shortened link does not exist!';
					}
				} else {
					/*
					 If the link was not checked by the API
					*/
					this.link.checked = false;
					if (dat.alert != null){
						this.showSection = 'error';
						this.error.msg = dat.alert.txt;
					} else {
						this.showSection = 'error';
						this.error.msg = 'An unknown error occured!';
					}
				}
			}).catch(function(error){this.showSection='error'; this.error.msg=error;});
		},
		countdown () {
			if ( this.msRemaining <= 0) {
				document.location.href = this.link.redirect.startsWith('http') ? this.link.redirect : 'https://'+this.link.redirect;
				clearInterval(this.timer);
			} else {
				this.msRemaining = this.msRemaining - 100;
			}
		}
	}
}
</script>
