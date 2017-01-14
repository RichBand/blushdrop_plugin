<?php
if( !class_exists( 'TPLSupport' ) ) :

	class TPLSupport {
		function verifyNonce(){
			$nonce     = isset( $_REQUEST[ $this->nonceId() ] ) ? $_REQUEST[ $this->nonceId() ] : null;
			$nonceText = $this->nonceText();
			if ( ! wp_verify_nonce( $nonce, $nonceText ) ) {
				return false;
			}
			return true;
		}

        function nonceText(){
        	return "tlp_team_nonce";
        }
		function nonceId() {
			return "tlp_nonce";
		}

        function socialLink(){
            return array(
                    'facebook' => __('Facebook', TLP_TEAM_SLUG),
                    'twitter'   => __('Twitter', TLP_TEAM_SLUG),
                    'linkedin' =>  __('LinkedIn', TLP_TEAM_SLUG),
                    'youtube' =>  __('Youtube', TLP_TEAM_SLUG),
                    'vimeo' =>  __('Vimeo', TLP_TEAM_SLUG),
                    'google-plus' =>  __('Google+', TLP_TEAM_SLUG)
                );
        }

	}
endif;
