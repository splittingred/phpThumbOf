<?php

require_once(dirname(__FILE__).'/utilities/credentials.class.php');

/**
 * Handles S3 operations
 */
class modAws {
    public $s3 = false;
    public $bucket = false;
    public $aws_key = null;
    public $aws_secret = null;
    
    function __construct(modX &$modx,array $config = array()) {
        $this->modx =& $modx;
        $this->config = array_merge(array(),$config);

        // Set AWS key
        if (is_null($this->aws_key)) {
            $this->aws_key = $modx->getOption('phpthumbof.s3_key',$config,'');
        }

        // Set AWS secret
        if (is_null($this->aws_secret)) {
            $this->aws_secret = $modx->getOption('phpthumbof.s3_secret_key',$config,'');
        }

        // NOTE: These can be set later, not needed now ....
        // $modx->getOption('aws.account_id',$config,''));
        // $modx->getOption('aws.canonical_id',$config,''));
        // $modx->getOption('aws.canonical_name',$config,''));
        // $modx->getOption('aws.mfa_serial',$config,''));
        // $modx->getOption('aws.cloudfront_keypair_id',$config,''));
        // $modx->getOption('aws.cloudfront_private_key_pem',$config,''));
        // $modx->getOption('aws.enable_extensions',$config,''));

        CFCredentials::set(array(

            // Credentials for the development environment.
            'development' => array(

                // Amazon Web Services Key. Found in the AWS Security Credentials. You can also pass
                // this value as the first parameter to a service constructor.
                'key' => $this->aws_key,

                // Amazon Web Services Secret Key. Found in the AWS Security Credentials. You can also
                // pass this value as the second parameter to a service constructor.
                'secret' => $this->aws_secret,

                // This option allows you to configure a preferred storage type to use for caching by
                // default. This can be changed later using the set_cache_config() method.
                //
                // Valid values are: `apc`, `xcache`, or a file system path such as `./cache` or
                // `/tmp/cache/`.
                'default_cache_config' => '',

                // Determines which Cerificate Authority file to use.
                //
                // A value of boolean `false` will use the Certificate Authority file available on the
                // system. A value of boolean `true` will use the Certificate Authority provided by the
                // SDK. Passing a file system path to a Certificate Authority file (chmodded to `0755`)
                // will use that.
                //
                // Leave this set to `false` if you're not sure.
                'certificate_authority' => false
            ),

            // Specify a default credential set to use if there are more than one.
            '@default' => 'development'
        ));

        include dirname(__FILE__).DIRECTORY_SEPARATOR.'sdk.class.php';

        $this->getS3();
        $this->setBucket($modx->getOption('phpthumbof.s3_bucket',$config,''));
    }

    public function getS3() {
        if ($this->s3) return $this->s3;
        
        $this->s3 = new AmazonS3();
        return $this->s3;
    }

    public function setBucket($bucket) {
        $this->bucket = $bucket;
    }
    public function bucketExists() {
        return $this->s3->if_bucket_exists($this->bucket);
    }
    public function createBucket($region = AmazonS3::REGION_US_W1) {
        $response = $this->s3->create_bucket($this->bucket,$region);
	return $response->isOK() ? true : false;
    }

    public function upload($file,$target = '',array $options = array()) {
        $options = array_merge(array(
            'acl' => AmazonS3::ACL_PUBLIC,
        ),$options);

        $individualFiles = array();
        if (is_array($file)) {
            $filename = basename($file);
            $file = $file['tmp_name'];
        } else {
            $filename = basename($file);
        }
        
        $options['fileUpload'] = $file;
        $response = $this->s3->create_object($this->bucket,$target.$filename,$options);
        if ($response->status != 200) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'[phpthumbof] Failed uploading '.$file.' to AWS in dir: '.$this->bucket.'/'.$target.' - '.(string)$response->body->Message);
            return false;
        }
        return $this->s3->get_object_url($this->bucket,$target.$filename);
    }

    public function getFileUrl($path,$expires = null) {
        return $this->s3->get_object_url($this->bucket,$path,$expires);
    }

    public function getObjectList($path = '',$opt = array()) {
        if (!empty($path)) {
            $opt['prefix'] = $path;
        }
        $objs = $this->s3->list_objects($this->bucket,$opt);
        $list = array();
        if ($objs && is_object($objs) && $objs->body && $objs->status == 200) {
            foreach ($objs->body->Contents as $obj) {
                $list[] = $obj;
            }
        }
        return $list;
    }

    public function deleteObject($path,$opt = array()) {
        return $this->s3->delete_object($this->bucket,$path,$opt);
    }
}