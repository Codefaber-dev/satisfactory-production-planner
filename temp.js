function imageHotspotComponent() {
    return {
        images: {
            floor1: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_1_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_1_-_G.png',
                x: 0,
                y: 0,
            },
            floor2: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_2_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_2_-_G.png',
                x: 0,
                y: 0,
            },
            floor3: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_3_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_3_-_G.png',
                x: 0,
                y: 0,
            },
            floor4: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_4_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_4_-_G.png',
                x: 0,
                y: 0,
            },
            floor5: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_5_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_5_-_G.png',
                x: 0,
                y: 0,
            },
            floor6: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_6_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_6_-_G.png',
                x: 0,
                y: 0,
            },
            floor7: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_7_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_7_-_G.png',
                x: 0,
                y: 0,
            },
            floor8: {
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_8_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_8_-_G.png',
                x: '35%',
                y: '30%',
            },
        },

        activeHotspot: null,
        activeInfo: null,
        hotspots: [
            {
                x: 33,
                y: 20,
                width: 23,
                height: 10,
                title: 'Floor 8 - Lorem Ipsum',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_8_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_8_-_G.png',
                description:
                    'This luxurious high-end apartment boasts a wealth of top-tier amenities designed to cater to your every need. Residents will enjoy a state-of-the-art fitness center, complete with a yoga studio and personal trainers, as well as an elegant rooftop terrace that offers breathtaking city views and a resort-style swimming pool. The apartment also features a private cinema, a fully-equipped business center, and an exclusive resident lounge for entertaining guests or simply unwinding after a long day. Additional amenities include a 24-hour concierge service, secure underground parking, and a pet spa, ensuring an unparalleled living experience for discerning individuals seeking the ultimate in comfort, convenience, and style.',
            },
            {
                x: 33,
                y: 30,
                width: 23,
                height: 10,
                title: 'Floor 7 - Dolor Sit Amet',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_7_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_7_-_G.png',
                description:
                    "This comfortable mid-tier apartment offers a pleasing array of amenities to suit the modern lifestyle. Residents can take advantage of the well-maintained fitness center, providing an ideal space for workouts and physical activity. The apartment complex also includes a welcoming community room where neighbors can gather for social events or relaxation. Outdoors, you'll find a charming courtyard featuring a sparkling swimming pool and a picnic area with barbecue grills, perfect for summertime gatherings. For added convenience, the apartment provides on-site laundry facilities, covered parking, and a professional management team dedicated to ensuring your living experience is both enjoyable and hassle-free.",
            },
            {
                x: 33,
                y: 40,
                width: 23,
                height: 10,
                title: 'Floor 6 - Consectetur Elit',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_6_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_6_-_G.png',
                description:
                    "This budget-friendly economy class apartment offers essential amenities and a cozy living space for those seeking an affordable yet comfortable home. The apartment complex features well-maintained grounds, providing a pleasant atmosphere for residents. On-site laundry facilities ensure that daily chores can be completed with ease, while the dedicated maintenance team is always available to address any concerns or repairs promptly. Residents will also appreciate the ample parking spaces, making coming home a breeze. The apartment's prime location offers convenient access to public transportation, local shops, and eateries, allowing residents to enjoy the neighborhood's offerings without breaking the bank.",
            },
            {
                x: 33,
                y: 50,
                width: 23,
                height: 10,
                title: 'Floor 5 - Lorem Elit',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_5_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_5_-_G.png',
                description:
                    "This inviting economy class apartment presents a cost-effective living solution with a range of essential amenities to accommodate residents' needs. The well-lit apartment grounds and common areas create a welcoming environment, while the on-site playground offers a safe and enjoyable space for children to play. The apartment complex also provides a dedicated bicycle storage area, encouraging eco-friendly transportation options. For added convenience, residents can access the on-site laundry facilities, making daily tasks more manageable. The apartment's strategic location ensures that public transportation, grocery stores, and various dining options are within easy reach, providing an economical and comfortable living experience.",
            },
            {
                x: 33,
                y: 60,
                width: 23,
                height: 10,
                title: 'Floor 4 - Ipsum Dolor Elit',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_4_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_4_-_G.png',
                description:
                    'This premium high-end office space for lease offers a prestigious business address and an unparalleled professional environment. The state-of-the-art building features a striking architectural design, floor-to-ceiling windows that flood the space with natural light, and expansive views of the city skyline. Tenants will benefit from cutting-edge technology infrastructure, high-speed internet connectivity, and advanced security systems. The office space also boasts a range of deluxe amenities, including an elegantly appointed reception area, fully-equipped conference rooms, and a stylish business lounge for informal meetings or networking events. With ample parking, 24-hour access, and a dedicated management team, this high-end office space provides an exceptional setting for businesses seeking to make a lasting impression on clients and partners alike.',
            },
            {
                x: 33,
                y: 70,
                width: 23,
                height: 10,
                title: 'Floor 3 - Ipsum Dolor Elit',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_3_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_3_-_G.png',
                description:
                    'This premium high-end office space for lease offers a prestigious business address and an unparalleled professional environment. The state-of-the-art building features a striking architectural design, floor-to-ceiling windows that flood the space with natural light, and expansive views of the city skyline. Tenants will benefit from cutting-edge technology infrastructure, high-speed internet connectivity, and advanced security systems. The office space also boasts a range of deluxe amenities, including an elegantly appointed reception area, fully-equipped conference rooms, and a stylish business lounge for informal meetings or networking events. With ample parking, 24-hour access, and a dedicated management team, this high-end office space provides an exceptional setting for businesses seeking to make a lasting impression on clients and partners alike.',
            },
            {
                x: 33,
                y: 80,
                width: 23,
                height: 10,
                title: 'Floor 2 - Ipsum Dolor Elit',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_2_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_2_-_G.png',
                description:
                    'This premium high-end office space for lease offers a prestigious business address and an unparalleled professional environment. The state-of-the-art building features a striking architectural design, floor-to-ceiling windows that flood the space with natural light, and expansive views of the city skyline. Tenants will benefit from cutting-edge technology infrastructure, high-speed internet connectivity, and advanced security systems. The office space also boasts a range of deluxe amenities, including an elegantly appointed reception area, fully-equipped conference rooms, and a stylish business lounge for informal meetings or networking events. With ample parking, 24-hour access, and a dedicated management team, this high-end office space provides an exceptional setting for businesses seeking to make a lasting impression on clients and partners alike.',
            },
            {
                x: 33,
                y: 90,
                width: 23,
                height: 10,
                title: 'Floor 1 - Ipsum Dolor Elit',
                hover: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_1_-_H.png',
                glow: 'https://res.cloudinary.com/codefaber/image/upload/v1683177059/wordpress-plugin/Floor_1_-_G.png',
                description:
                    'The grand lobby of this high-end apartment building exudes sophistication and elegance, making a lasting impression upon residents and guests alike. As you enter, you are greeted by a soaring, double-height ceiling adorned with a statement chandelier that bathes the space in a warm, inviting glow. The luxurious marble flooring and tastefully curated artwork reflect the refined design aesthetic found throughout the building. A 24-hour concierge, clad in professional attire, stands ready at the reception desk to assist with any needs or requests, ensuring a seamless living experience. The lobby also features a comfortable seating area with plush, designer furnishings, creating a sophisticated yet inviting ambiance for casual conversations or waiting for a car service. This exquisite lobby sets the tone for the unrivaled luxury and attention to detail that permeate every aspect of this exceptional apartment building.',
            },
        ],
        showGlow(index) {
            this.activeHotspot = index;
        },
        hideGlow() {
            this.activeHotspot = null;
        },
        showInfo(index) {
            this.activeInfo = this.hotspots[index];
        },
    };
}
