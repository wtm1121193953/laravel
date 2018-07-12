<template>
    <page title="分享会员二维码">
        <div class="qrcode-container">
            <div class="title">分享会员二维码 <el-button type="text" @click="showExample = true">示例</el-button></div>
            <img class="image" :src="qrcodeUrl" alt="">
            <div class="btns">
                <el-button type="text" @click="download(1)">下载（小）</el-button>
                <el-button type="text" @click="download(2)">下载（中）</el-button>
                <el-button type="text" @click="download(3)">下载（大）</el-button>
            </div>
            <div class="tips">
                <div class="tip">小：8cm（正方形，边长）</div>
                <div class="tip">中：15cm</div>
                <div class="tip">大：50cm</div>
            </div>
        </div>
        <el-dialog :visible.sync="showExample" width="290px">
            <div class="example-title">注册二维码</div>
            <img class="image" :src="qrcodeUrl" alt="">
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "invite-channel",
        data(){
            return {
                showExample: false,
                qrcodeUrl: ''
            }
        },
        methods: {
            download(type){
                location.href = '/api/merchant/inviteChannel/downloadInviteQrcode?type=' + type
            },
            init(){
                api.get('/inviteChannel/inviteQrcode').then(data => {
                    this.qrcodeUrl = data.qrcode_url;
                })
            }
        },
        created(){
            this.init();
        }
    }
</script>

<style scoped>
    .qrcode-container{
        margin-left: 20px;
    }
    .qrcode-container .title {

    }
    .example-title {
        font-size: 20px;
        font-weight: 600;
        text-align: center;
    }
    .image {
        width: 250px;
        height: 250px;
        margin-top: 20px;
    }
    .tips {
        margin-top: 20px;
        margin-left: 20px;
        color: #555;
        font-size: 14px;
        line-height: 24px;
    }
</style>