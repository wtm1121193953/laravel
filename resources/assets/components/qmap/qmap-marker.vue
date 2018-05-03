<template>
    <div>
        <slot/>
    </div>
</template>

<script>
    /**
     * 腾讯地图marker组件
     */
    export default {
        name: "qmap-marker",
        props: {
            value: {type: Array,}, // marker的值, 一个数组, 第一个元素是经度, 第二个元素是纬度
            draggable: {type: Boolean, default: false}, // marker是否可拖拽, 默认不可拖拽
            showInfoWindow: {type: Boolean, default: true}, // 是否显示信息窗体 todo
        },
        inject: ['manager'],
        data(){
            return {
                marker: null, // marker实例
                infoWindow: null, // marker上的 infoWindow 实例
            }
        },
        computed: {
            map(){
                return this.manager.getMap();
            }
        },
        methods: {
            init(){
                // 初始化操作
                if(!this.map){
                    // 如果地图尚未加载完成, 延迟100毫秒后再执行
                    setTimeout(() => {this.init()}, 100)
                }else {
                    let latLng = new qq.maps.LatLng(this.value[1], this.value[0]);
                    this.marker = new qq.maps.Marker({
                        map: this.map,
                        position: latLng,
                        draggable: this.draggable,
                    });
                    if(this.draggable){
                        // 设置marker拖拽操作结束的事件
                        qq.maps.event.addListener(this.marker, 'dragend', (e) => {
                            let value = [this.marker.getPosition().getLng(), this.marker.getPosition().getLat()];
                            this.$emit('change', value);
                            this.$emit('input', value)
                            if(this.showInfoWindow){
                                this.changeInfoWindow();
                            }
                        });
                    }
                    if(this.showInfoWindow){
                        // 初始化信息窗口
                        this.initInfoWindow();
                    }
                }
            },
            initInfoWindow(){
                // 初始化信息窗口
                this.infoWindow = new qq.maps.InfoWindow({
                    map: this.map,
                    content: 'content',
                    position: this.marker,
                    // zIndex
                });
                // 监听marker点击事件, 点击时展示信息窗口
                qq.maps.event.addListener(this.marker, "click", (e) => {
                    this.changeInfoWindow();
                });
            },
            changeInfoWindow(){
                // marker设置后设置infoWindow内容
                let geocoder = new qq.maps.Geocoder();
                geocoder.getAddress(this.marker.getPosition());
                geocoder.setComplete((result) => {
                    let poi = result.detail.nearPois[0] || {};
                    let address = poi.address || result.detail.address;
                    let title = poi.name || '';
                    this.infoWindow.setPosition(this.marker);
                    this.infoWindow.setContent(`<div class="info-window-title">${title}</div><div class="info-window-address">${address}</div>`);
                    this.infoWindow.open();
                });
            }
        },
        created(){
            this.init();
        },
        watch: {
            value: {
                deep: true,
                handler(val){
                    let latLng = new qq.maps.LatLng(this.value[1], this.value[0]);
                    this.marker.setPosition(latLng);
                }
            }
        }
    }
</script>
<style>
    .info-window-title {
        font-size: 18px;
        color: #5bb1fa;
    }
</style>
<style scoped>

</style>