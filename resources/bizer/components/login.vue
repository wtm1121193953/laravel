<style lang="less" scoped>
    .login-container {
        width: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        /*background-color: #141a48;*/
        background-image: url(../images/login-bg.png);
        background-repeat: no-repeat;
        background-size: cover;
        overflow: hidden;
    }
    /*#loginThree {
        position: absolute;
        width: 100%;
        top: 0;
        bottom: 0;
        overflow: hidden;
    }*/
    .form-fade-enter-active, .form-fade-leave-active {
        transition: all 1s;
    }
    .form-fade-enter, .form-fade-leave-active {
        transform: translate3d(0, -50px, 0);
        opacity: 0;
    }
</style>
<template>
    <div class="login-container">
        <transition name="form-fade" mode="out-in">
            <router-view></router-view>
        </transition>
        <!--<div id="loginThree"></div>-->
    </div>
</template>

<script>
    // import THREE from '../../assets/js/three/three';

    export default {
        data(){
            return {
            }
        },
        computed:{

        },
        methods: {
/*
            init3D () { // 初始化3D动画
                var SCREEN_WIDTH = window.innerWidth;
                var SCREEN_HEIGHT = window.innerHeight;
                var SEPARATION = 90;
                var AMOUNTX = 50;
                var AMOUNTY = 50;
                var container;
                var particles, particle;
                var count;
                var camera;
                var scene;
                var renderer;
                var mouseX = 0;
                var mouseY = 0;
                var windowHalfX = window.innerWidth / 2;
                var windowHalfY = window.innerHeight / 2;
                init();
                this.interval = setInterval(loop, 1000 / 60);
                function init() {
                    container = document.createElement( 'div' );
                    container.style.position = 'relative';
                    container.style.top = '200px';
                    document.getElementById('loginThree').appendChild( container );
                    camera = new THREE.Camera( 75, SCREEN_WIDTH / SCREEN_HEIGHT, 1, 10000 );
                    camera.position.z = 1000;
                    scene = new THREE.Scene();
                    renderer = new THREE.CanvasRenderer();
                    renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );
                    particles = new Array();
                    var i = 0;
                    var material = new THREE.ParticleCircleMaterial( 0x097bdb, 1 );
                    for ( var ix = 0; ix < AMOUNTX; ix ++ ) {
                        for ( var iy = 0; iy < AMOUNTY; iy ++ ) {
                            particle = particles[ i ++ ] = new THREE.Particle( material );
                            particle.position.x = ix * SEPARATION - ( ( AMOUNTX * SEPARATION ) / 2 );
                            particle.position.z = iy * SEPARATION - ( ( AMOUNTY * SEPARATION ) / 2 );
                            scene.add( particle );
                        }
                    }
                    count = 0;
                    container.appendChild( renderer.domElement );
                    document.addEventListener( 'mousemove', onDocumentMouseMove, false );
                    document.addEventListener( 'touchmove', onDocumentTouchMove, false );
                }
                function onDocumentMouseMove( event ) {
                    mouseX = event.clientX - windowHalfX;
                    mouseY = event.clientY - windowHalfY;
                }
                function onDocumentTouchMove( event ) {
                    if ( event.touches.length == 1 ) {
                        event.preventDefault();
                        mouseX = event.touches[ 0 ].pageX - windowHalfX;
                        mouseY = event.touches[ 0 ].pageY - windowHalfY;
                    }
                }
                function loop() {
                    camera.position.x += ( mouseX - camera.position.x ) * .05;
                    camera.position.y = 364;
                    var i = 0;
                    for ( var ix = 0; ix < AMOUNTX; ix ++ ) {
                        for (var iy = 0; iy < AMOUNTY; iy++) {
                            particle = particles[ i++ ];
                            particle.position.y = ( Math.sin( ( ix + count ) * 0.3 ) * 50 ) + ( Math.sin( ( iy + count ) * 0.5 ) * 50 );
                            particle.scale.x = particle.scale.y = ( Math.sin( ( ix + count ) * 0.3 ) + 1 ) * 2 + ( Math.sin( ( iy + count ) * 0.5 ) + 1 ) * 2;
                        }
                    }
                    renderer.render(scene, camera);
                    count += 0.1;
                }
            }*/
        },
        created: function () {
        },
        mounted () {
            const that = this;
            // that.init3D();
        },
        beforeDestroy () {
            // const self = this
            // if (self.interval) clearInterval(self.interval);
        }
    }
</script>

