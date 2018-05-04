<template>
    <div class="container" :style="{width: width}">
        <el-input ref="keywordInput" :value="keyword" @keyup.native.enter="search" @input="keywordChange" placeholder="请输入关键字搜索">
            <el-button slot="append" icon="el-icon-search" @click="search"/>
        </el-input>
    </div>
</template>

<script>
    /**
     * 腾讯地图搜索组件
     */
    export default {
        name: "qmap-search-bar",
        props: {
            keyword: {type: String, default: ''}, // 搜索关键词
            cityName: {type: String, default: ''}, // 自动补全控件及搜索服务的城市名
            toMap: {type: Boolean, default: false}, // 是否将搜索结果直接展现到地图中
            searchLimit: {type: Number, default: 1}, // 搜索数量限制

            autoComplete: {type: Boolean, default: true}, // 搜索框是否使用自动补全控件

            width: {type: String, default: '300px'}, // 搜索栏宽度
        },
        inject: ['manager'],
        computed: {
            map(){ // qmap 组件
                return this.manager.getMap();
            },
            qmap(){ // 腾讯地图实例
                return this.map.qmap;
            }
        },
        data() {
            return {
                keywordValue: '',
                searchService: null, // 搜索服务实例
                autoCompletePlugin: null, // 自动补全控件
            }
        },
        methods: {
            searchComplete(results){
                let pois = results.detail.pois;
                let positions = [];
                for(let i = 0,l = pois.length;i < l; i++){
                    let latLng = pois[i].latLng;
                    positions.push([latLng.getLng(), latLng.getLat()]);
                }
                this.$emit('search', positions);
                this.map.resetLatlngBounds();
            },
            init(){
                // 初始化控件
                if(!this.qmap){
                    setTimeout(() => {this.init()}, 100)
                }else {
                    // 初始化搜索服务
                    let SearchOptions = {
                        complete: (results) => {this.searchComplete(results)},
                        pageCapacity: this.searchLimit,
                        location: this.cityName,
                    };
                    if(this.toMap){
                        SearchOptions.map = this.qmap;
                    }
                    this.searchService=new qq.maps.SearchService(SearchOptions);

                    if(this.autoComplete){
                        // 初始化自动补全控件 腾讯地图的自动补全zindex为1, 且无法修改, 在弹框中显示不出来
                        this.autoCompletePlugin = new qq.maps.place.Autocomplete(this.$refs.keywordInput.$refs.input, {
                            location: this.cityName,
                        });
                        //添加监听事件
                        qq.maps.event.addListener(this.autoCompletePlugin, "confirm", (res) => {
                            this.searchService.search(res.value);
                        });
                    }

                }
            },
            keywordChange(value){
                this.keywordValue = value;
                this.$emit('update:keyword', value);
            },
            search(){
                this.searchService.search(this.keywordValue);
            }
        },
        created(){
            this.keywordValue = this.keyword;
            this.init();
        },
        watch: {
            keyword(val){
                this.keywordValue = val;
            }
        }
    }
</script>

<style scoped>
    .container {
        position: absolute;
        top: 10px;
        left: 10px;
    }
</style>