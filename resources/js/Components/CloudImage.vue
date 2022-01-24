<template>
    <img :src="url" :alt="alt">
</template>

<script>
import {Cloudinary} from 'cloudinary-core';

const cl = Cloudinary.new({cloud_name : 'codefaber'});

export default {
    name: "CloudImage",

    props: {
        alt: String,
        publicId: String,
        crop: String,
        width: Number,
        height: Number,
        quality: Number,
    },

    computed: {
        url() {
            let opts = {},
                publicId = this.publicId.replace(/ /gi,'').replace(/Packaged/gi,'').replace(/\-/gi,'').replace(/.png/,'');
            if (this.crop) {
                opts.crop = this.crop;
            }
            if (this.width) {
                opts.width = this.width;
            }
            if(this.height) {
                opts.height = this.height;
            }
            if(this.quality) {
                opts.quality = this.quality;
            }

            return cl.url('satisfactory/' + publicId + ".png", opts);
        }
    }
}
</script>
