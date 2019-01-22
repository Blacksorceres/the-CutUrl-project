<template>
  <v-content class="fill-height pt-0">
    <v-container fluid fill-height>
      <v-layout align-center justify-center>
        <v-flex xs12 sm10 md10 lg8 xl6>
          <v-card class="elevation-12" dark>
            <v-card-text class="text-xs-center">
              <v-layout row wrap>
                <v-flex xs12 class="px-2">
                  <v-text-field
                    v-model="uri_value"
                    label="Insert your long URI"
                    :disabled="disabled"
                    :prepend-icon="$vuetify.breakpoint.mdAndUp ? 'mdi-link-variant' : ''"
                    :messages="uri_message"
                    :error="uri_okay === false"
                    :success="uri_okay === true"
                  ></v-text-field>
                </v-flex>
                <v-flex xs12 class="mt-2">
                  <v-expansion-panel
                    v-model="extra_show"
                    expand
                    :disabled="disabled"
                  >
                    <v-expansion-panel-content class="elevation-5">
                      <div slot="header">Show extra</div>
                      <v-card>
                        <v-container fluid>
                          <v-layout row wrap>
                            <v-flex xs12 md6 class="px-2">
                              <v-text-field
                                v-model="pwd_value"
                                label="Protect it with password"
                                :prepend-icon="$vuetify.breakpoint.mdAndUp ? 'mdi-lock-question' : ''"
                                :disabled="disabled"
                                :type="pwd_show ? 'text' : 'password'"
                                :messages="pwd_message"
                                :error="pwd_okay === false"
                                :success="pwd_okay === true"
                              >
                                <v-tooltip slot="append" top>
                                  <v-icon
                                    slot="activator"
                                    dark
                                    @click="pwd_show = !pwd_show"
                                  >{{ pwd_show ? 'mdi-eye' : 'mdi-eye-off' }}</v-icon>
                                  <span>{{ pwd_show ? 'Hide password' : 'Show password' }}</span>
                                </v-tooltip>
                              </v-text-field>
                            </v-flex>
                            <v-flex xs12 md6 class="px-2">
                              <v-text-field
                                v-model="custom_value"
                                :label="'Custom ending - '+domain_value+'/'+(custom_value!=''?custom_value:'_ _ _ _ _ _')"
                                :disabled="disabled"
                                :readonly="custom_loading"
                                :loading="custom_loading"
                                :messages="custom_message"
                                :append-icon="$vuetify.breakpoint.mdAndUp ? custom_icon : ''"
                                :error="custom_success === false"
                                :success="custom_success === true"
                              ></v-text-field>
                            </v-flex>
                            <v-flex xs12 md6 class="px-2">
                              <v-select
                                :items="domain_values"
                                v-model="domain_value"
                                menu-props="auto"
                                label="Domain"
                                hide-details
                                prepend-icon="mdi-domain"
                                single-line
                              ></v-select>
                            </v-flex>
                          </v-layout>
                          <v-divider class="mt-4 mb-1" />
                          <v-layout row wrap>
                            <v-flex xs12 sm12 md6 class="px-2">
                              <v-switch
                                v-model="ads_allowed"
                                color="info"
                                :label="ads_allowed?'Display ads':'Do not display ads'"
                                :disabled="disabled"
                              ></v-switch>
                            </v-flex>
                            <v-flex xs12 sm12 md6 class="px-2">
                              <v-switch
                                v-model="instant_value"
                                color="info"
                                :label="instant_value?'Instant redirect':'Delayed redirect'"
                                :disabled="disabled"
                              ></v-switch>
                            </v-flex>
                          </v-layout>
                        </v-container>
                      </v-card>
                    </v-expansion-panel-content>
                  </v-expansion-panel>
                </v-flex>
              </v-layout>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                outline
                right
                fab
                small
                color="info"
                @click="dialogs.extra_show = true"
              >
                <v-icon>mdi-help</v-icon>
              </v-btn>
              <v-btn
                outline
                color="green"
                :disabled="disabled || !global_okay"
                @click="postData"
              >
                <span>Cut this URI</span>
                <v-icon dark right v-if="!disabled">mdi-send</v-icon>
                <v-icon dark right v-if="disabled" class="spin-pulse-10 spin-reverse">mdi-cached</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>
      </v-layout>
    </v-container>
    <v-dialog
      dark
      persistent
      v-model="dialogs.success_show"
      max-width="768"
    >
      <v-card>
        <v-card-title class="headline">Successfully shortened URI</v-card-title>
        <v-card-text>
          <v-container>
            <v-layout row>
              <v-flex xs12 md6>
                <v-card
                  class="elevation-3"
                >
                  <v-layout row class="px-1">
                    <v-flex
                      xs10
                      style="padding: 8px 0px 0px 8px;"
                    >
                      <a
                        style="font-size:20px;"
                        class="default"
                        target="_blank"
                        :href="shortenedLink"
                      >
                        {{ shortenedLink }}
                      </a>
                    </v-flex>
                    <v-flex xs2>
                      <v-btn
                          flat
                          style="min-width:unset;padding-left:12px;padding-right:12px;"
                          v-clipboard:copy="shortenedLink"
                      >
                        <v-tooltip top>
                            <v-icon
                              slot="activator"
                              dark
                              @click="snackbars.copied = true"
                          >
                              mdi-content-copy
                          </v-icon>
                          <span>Copy short url</span>
                        </v-tooltip>
                      </v-btn>
                    </v-flex>
                  </v-layout>
                </v-card>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            outline
            color="info"
            @click="dialogs.success_show=false; resetForm();"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      dark
      v-model="dialogs.extra_show"
      max-width="768"
    >
      <v-card>
        <v-card-title class="headline">Extra options - help</v-card-title>
        <v-card-text class="dialog-help-text">
          <h2>Main (required) inputs</h2>
          <h3>The long URI *required</h3>
          <p>
            >> The "long uri" input field is used to insert your long URI (address / link) that will be shortened to a shorter one (~20 characters in total by default).
          </p>
          <h2>Extra</h2>
          <h3>Password *optional</h3>
          <p>
            >> If you insert any phrase in the password field, the shortened link that you will create will be protected with it. When you share the short link with others, you will have to tell them the passphrase, so they will be able to access it's content.
            <br>
            >> Why is this added? -Because why not.
          </p>
          <h3>Your custom ending *optional</h3>
          <p>
            >> Custom ending allows you to have a custom ending of the shortened link. If the prefered ending is already taken, the input field will be colored red. If it is free, and you can use it, the input field will be colored green.
          </p>
          <h3>Allow or disallow ads *optional</h3>
          <p>
            >> With this option, you can allow or disallow us to display ads. When someone visits your shortened link, a pay will be displayed with a five-second-loader. There will be a few advertisement boxes on the page, which will redirect the user to your original URL after five seconds.
            <br>
            >> But why ads? -This app is free to use for everyone, and we'll keep it that way. But we also need some resources in order to keep the servers running. With you allowing us to display ads on your shortened link, we can get enough money to pay for the costs.
          </p>
          <h2>Action buttons *required</h2>
          <h3>The "cut this uri" button</h3>
          <p>
            >> The "cut this uri" button will send all the inserted data to the server that will check, validate and process the provided data. If everything is okay, the URI will be shortened and you will be correctly informed about the successfull action.
            <br>
            >> Why I can not click on this button? -Probably, because something is not okay. Please check that all input fields are marked as valid and that you have provided the data on all required fields.
          </p>
          <h2>I am having some other issue</h2>
          <h3>I want to report a shortened link that redirects to;</h3>
          <p>
            - a website that attempts to scam people <br>
            - a website that has inappropriate content <br>
            - is using an inappropriate custom link ending <br>
            - is using an inappropriate custom link and redirects to my sodial media / blog / website / etc. <br>
            - any web service that is breaking any law, is harmful, attempts to harm or scam others, etc. <br>
            <br>
            If you want to report any shortened link, please do contact me at <a href="mailto:aljaxus.dev@gmail.com">aljaxus.dev (at) gmail.com</a>.
          </p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            outline
            color="info"
            @click="dialogs.extra_show = false"
          >
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-snackbar
      v-model="snackbars.copied"
      color="success"
      left
      :timeout="1700"
    >
      Url copied to clipboard
    </v-snackbar>
  </v-content>
</template>

<script>
import store from './../store.js';
import axios from 'axios';
var _ = require('lodash');

export default {
  data () {
    return {
      store: store,
      dialogs: {
        extra_show: false,
        success_show: false,
        error_show: false
      },
      linkData: {
        original: '',
        pwd: '',
        domain: '',
        short: '',
        ads: false
      },
      snackbars: {
        copied: false
      },
      disabled: false,
      uri_value: '',
      uri_okay: null,
      uri_message: '',
      extra_show: [false],
      domain_value: 'cuturl.it',
      domain_values: ['cuturl.it', 'pofuki.me', 'tnmc.link', 'foxeh.eu'],
      custom_loading: false,
      custom_value: '',
      custom_message: '',
      custom_success: null,
      custom_icon: '',
      pwd_value: '',
      pwd_okay: null,
      pwd_message: '',
      pwd_show: false,
      ads_allowed: true,
      instant_value: false
    }
  },
	computed: {
    global_okay () {return this.custom_success!==false && this.uri_okay==true && this.pwd_okay!==false},
    shortenedLink () {return 'https://'+this.linkData.domain+'/'+this.linkData.short}
	},
	watch: {
    // eslint-disable-next-line
		uri_value: function (newUri, oldUri) {
      // eslint-disable-next-line
			if (!!newUri){
        // eslint-disable-next-line
				if (newUri.match(/^(https?:\/\/)?[a-z0-9\.\-]*\.[a-z0-9]{2,9}(((\/|\?)([a-z0-9\/<>!\?\=\&_\-\#\.\+\%]*)))?$/igm)) {
					this.uri_okay = true;
					this.uri_message = '';
				} else {
					this.uri_okay = false;
					this.uri_message = 'Please insert a valid URL';
				}
			} else {
				this.uri_okay = null;
				this.uri_message = 'Required.'
			}
    },
    // eslint-disable-next-line
		pwd_value: function (newPwd, oldPwd) {
      // eslint-disable-next-line
			if (!!newPwd){
				if (newPwd.length <= 125) {
					this.pwd_okay = true;
					this.pwd_message = '';
				} else {
					this.pwd_okay = false;
					this.pwd_message = 'Max 125 characters - '+newPwd.length+'/125';
				}
			} else {
				this.pwd_okay = null;
				this.pwd_message = '';
			}
    },
    // eslint-disable-next-line
		custom_value: function (newCustom, oldCustom) {
      // eslint-disable-next-line
			if (!!newCustom){
				if (newCustom.length <= 32) {
					if (newCustom.match(/^([a-z0-9_-]*)$/i)) {
						this.custom_success = null;
						this.custom_icon = '';
						this.custom_message = '';
						this.debouncedCheckCustom()
					} else {
						this.custom_success = false;
						this.custom_icon = 'mdi-close';
						this.custom_message = 'Please insert a valid ending';
					}
				} else {
					this.custom_success = false;
					this.custom_icon = 'mdi-close';
					this.custom_message = 'Max 32 characters - '+newCustom.length+'/32';
				}
			} else {
				this.custom_success = null;
				this.custom_message = ''
				this.custom_icon = '';
			}
		}
	},
	created: function () {
		this.debouncedCheckCustom = _.debounce(this.checkCustom, 750)
	},
	methods: {
		checkCustom: function() {
			if (this.custom_value != ''){
				this.custom_loading = true;
        axios.get('https://cuturl.it/api/cut_checkshort.php', {
          params: { short: this.custom_value, domain: this.domain_value }
        }).then(response => {
          let dat = response.data;
          this.custom_loading = false;
          if (dat.data.checked == true){
            // It is checked
            if (dat.data.free == true){
              this.custom_icon = 'mdi-check';
              this.custom_success = true;
              this.custom_message = '';
            } else {
              this.custom_icon = 'mdi-close';
              this.custom_success = false;
              this.custom_message = 'This custom ending is already in use';
            }
          } else {
            this.custom_icon = 'mdi-close';
            this.custom_success = false;
            if (dat.alert != null){
              this.custom_message = dat.alert.txt;
            } else {
              this.custom_message = 'An unknown error occured';
            }
          }
        // eslint-disable-next-line
        }).catch(error => console.log(error));
			} else {
				this.custom_loading = false;
				this.custom_success = null;
				this.custom_message = '';
				this.custom_icon = '';
			}
		},
		postData: function() {
			this.disabled = true;
			this.extra_show = [true];

			setTimeout(()=>{
				axios.post('https://cuturl.it/api/cut_createnew.php', {
					original: this.uri_value,
          pwd: this.pwd_value,
          domain: this.domain_value,
					short: this.custom_value,
					ads: this.ads_allowed,
					instant: this.instant_value
				}).then(response => {
					let dat = response.data;
					if (dat.inserted == true){
						this.linkData.original	= dat.data.original;
						this.linkData.pwd		= dat.data.pwd;
						this.linkData.domain	= dat.data.domain;
						this.linkData.short		= dat.data.short;
						this.linkData.ads		= dat.data.ads;
						this.linkData.instant		= dat.data.instant;
						this.dialogs.success_show = true;
					} else {
						this.dialogs.error_show = true;
						this.disabled = false;
          }
          // eslint-disable-next-line
				}).catch(error => console.log(error));
			}, Math.floor(Math.random() * Math.floor(1200)));
		},
		resetForm: function () {
			this.disabled			     = false;
			this.uri_value		    	= '';
			this.uri_message	    	= '';
			this.uri_okay		      	= null;
			this.extra_show		    	= [false];
			this.domain_value	    	= 'cuturl.it';
			this.custom_loading	  	= false;
			this.custom_value	  	  = '';
			this.custom_message		  = '';
			this.custom_icon	    	= '';
			this.custom_success	  	= null;
			this.pwd_value		    	= '';
			this.pwd_message	  	  = '';
			this.pwd_show		      	= false;
			this.pwd_okay	    		  = null;
			this.ads_allowed	  	  = true;

			this.linkData.original  = '';
			this.linkData.pwd		    = '';
			this.linkData.short		  = '';
			this.linkData.ads		    = false;
		}
	}
}
</script>
