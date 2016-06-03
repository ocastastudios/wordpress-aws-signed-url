# AWS Signed URL Plugin #

This plugin will generate an Signed URL to enable private content to be served through Amazon CloudFront. A CloudFront
key pair has to be created to configure the plugin and the CloudFront distribution configured appropriately. 
Full details of this process can be found in the 
[AWS documentation](http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/PrivateContent.html)

This plugin does not manage the process of getting Media Assets on to Amazon S3 or modify the Domain name for AWS based
media. In order to do that a plugin such as Delicious Brains' [WP Offload S3](https://wordpress.org/plugins/amazon-s3-and-cloudfront/)
should be used.



