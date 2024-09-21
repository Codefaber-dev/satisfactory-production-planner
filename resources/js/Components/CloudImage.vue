<template>
    <img :src="url" :alt="alt" :height="height" :width="width" />
</template>

<script>
import { Cloudinary } from 'cloudinary-core';

const cl = Cloudinary.new({ cloud_name: 'dgev9coex' });

export default {
    name: 'CloudImage',

    props: {
        alt: String,
        publicId: String,
        crop: String,
        width: String,
        height: String,
        quality: String,
    },

    computed: {
        url() {
            let opts = {},
                publicId = this.publicId
                    .replace(/ /gi, '')
                    .replace(/Packaged/gi, '')
                    .replace(/-/gi, '')
                    .replace(/.png/, '');
            if (this.crop) {
                opts.crop = this.crop;
            }
            if (this.width) {
                opts.width = this.width;
            }
            if (this.height) {
                opts.height = this.height;
            }

            opts.quality = this.quality || 80;

            return cl.url('/' + publicId + '.webp', opts);
        },
    },
};
</script>
