<template>
    <div class="map-container" :style="{width: width, height: height}">
        <el-amap-search-box class="search-box" :search-option="searchOption" :on-search-result="onSearchResult"></el-amap-search-box>
        <el-amap ref="map" :amap-manager="amapManager" :vid="'map-vid-' + mapId" :center="center" :events="events">
            <el-amap-marker v-if="markerPosition" :vid="'amap-marker-vid-' + mapId" :position="markerPosition"></el-amap-marker>
        </el-amap>
        <el-button type="primary" size="small" @click="sureChoose" class="sure-button">确定</el-button>
    </div>
</template>

<script>
    import { AMapManager } from 'vue-amap';
    let amapManager = new AMapManager();
    export default {
        name: "amap-choose-point",
        props: {
            width: {type: String, default: '100%'},
            height: {type: String, default: '100%'},
            center: {type: Array, default: () => [114.047667,22.555086]},
            city: {type: String, default: '深圳市'},
            value: {type: Array, default: null},
        },
        data(){
            return {
                searchOption: {
                    city: this.city,
                },
                mapId: '',
                amapManager: amapManager,
                events: {
                    init: (o) => {

                    },
                    'moveend': () => {
                    },
                    'zoomchange': () => {
                    },
                    'click': (e) => {
                        // console.log(e);
                        let position = e.lnglat;
                        this.markerPosition = [position.lng, position.lat]
                    }
                },
                markerPosition: null
            }
        },
        methods: {
            onSearchResult(pois){
                // console.log(pois);
                if (pois.length > 0) {
                    this.markerPosition = [pois[0].lng, pois[0].lat];
                    this.center = deepCopy(this.markerPosition);
                }
            },
            sureChoose(){
                this.$emit('input', this.markerPosition);
                this.$emit('select', this.markerPosition);
            }
        },
        created(){
            this.mapId = '' + Math.random();
            if(this.value){
                this.markerPosition = this.value;
            }
        },
        mounted(){
            if(this.city){
                setTimeout(() => {
                    this.amapManager.getMap().setCity(this.city);
                }, 800)
            }
        }
    }
</script>

<style scoped>
    .map-container {
        position: relative;
    }
    .search-box {
        position: absolute;
        top: 10px;
        left: 20px;
        height: 35px;
    }
    .sure-button {
        position: absolute;
        top: 12px;
        left: 400px;
    }
</style>