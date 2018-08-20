<template>
    <page title="TPS会员帐号管理">
        <div>
            <div v-if="bindInfo == null" class="title">
                <el-tag type="danger">请联系客服进行TPS账号绑定</el-tag>
            </div>
            <div v-if="bindInfo" class="title">
                <el-tag type="success">已绑定TPS帐号：{{bindInfo.tps_account}}</el-tag>
            </div>
            <div class="tips m-t-20">
                <div class="tip">温馨提示：</div>
                <div class="tip">1、绑定TPS帐号后，您在大千生活的下级用户对您贡献的消费额可以按系数转化成TPS消费额。</div>
                <div class="tip">2、大千生活消费额置换TPS积分公式为：大千消费额/6/6.5/4，例如600大千消费额可以置换TPS积分=600/6/6.5/4=3.84个</div>
                <div class="tip">3、大千消费额与TPS消费额置换比为6：1</div>
            </div>
        </div>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "tps-bind",
        data(){
            return {
                bindInfo: '',
            }
        },
        methods: {
            init(){
                api.get('/tps/getBindInfo').then(data => {
                    this.bindInfo = data;
                })
            },
        },
        created(){
            this.init();
        }
    }
</script>
