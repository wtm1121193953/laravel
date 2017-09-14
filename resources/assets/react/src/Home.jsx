import React, {Component} from 'react';

import {Layout, Menu, Switch} from 'element-react';
import Header from './components/Header';
import Footer from './components/Footer';
import LeftMenu from './components/LeftMenu';
import Content from './components/Content';


class Home extends Component {
    constructor(props){
        super(props);

        this.state = {
            theme: 'light', // 系统主题
            menus: [{id: 0, name: '首页', url: '/'}],
            username: 'test username'
        };

        this.onSelect = this.onSelect.bind(this);
    }
    componentDidMount(){
        // todo 判断是否登录, 未登录跳转到登录页面
    }
    onSelect(index){
        console.log(index);
    }
    render(){
        return (<div>
            <Layout.Row>
                <Menu mode="horizontal" theme={ this.state.theme } onSelect={this.onSelect}>
                    <Menu.Item index="1" style={ {float: 'right'} }>退出</Menu.Item>
                    <Menu.Item index="1" style={ {float: 'right'} }>{this.state.username}</Menu.Item>
                    <Menu.Item index="1" style={ {float: 'right'} }>
                        主题
                        <Switch
                            value={this.state.theme}
                            onValue="light"
                            offValue="dark"
                            onColor="#E5E9F2"
                            offColor="#324057"
                            onText="light"
                            offText="dark"
                            width={70}
                            onChange={(value)=>{this.setState({theme: value})}}>
                        </Switch>
                    </Menu.Item>

                </Menu>
            </Layout.Row>
            <Layout.Row>
                <Layout.Col span="3">
                    <LeftMenu
                        theme={this.state.theme}
                        menus={this.state.menus}
                    />

                </Layout.Col>
                <Layout.Col span="21">
                    <Content/>
                </Layout.Col>
            </Layout.Row>
            <Layout.Row>
                <Footer/>
            </Layout.Row>
        </div>);
    }
}

export default Home;