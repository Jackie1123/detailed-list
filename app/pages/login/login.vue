<template>
	<view class="sun-index">
		<u-navbar :is-back="false" title="欢迎"></u-navbar>
		<view class="sun-logo-box">
			<view class="sun-logo">
				<image class="sun-icon-img" src="@/static/login/logo.png" />
			</view>
		</view>

		<u-row>
			<u-col :span="span" :offset="offset">
				<view class="sun-login-box">
					<view class="sun-label">
						<image style="width: 17px;height:17px;" src="@/static/login/account.png" />
						<text class="label-text">用户名</text>
					</view>
					<view class="sun-input-box">
						<input v-model="username" type="text" placeholder="请输入用户名"
							placeholder-class="placeholder-class" />
						<image @click="username=''" class="close-icon" src="@/static/login/close_icon.png" />
					</view>
				</view>
				<view class="sun-login-box">
					<view class="sun-label">
						<image style="width: 17px;height:17px;" src="@/static/login/password.png" />
						<text class="label-text">密码</text>
					</view>
					<view class="sun-input-box">
						<input v-model="password" type="password" placeholder="请输入密码"
							placeholder-class="placeholder-class" />
						<image @click="password=''" class="close-icon" src="@/static/login/close_icon.png" />
					</view>
				</view>
				<view class="sun-tip">
					<navigator class="sun-tip-text" url="../register/register">没有账号？注册</navigator>
				</view>
				<view class="login-btn-box">
					<view class="login-btn" @click="login">登录</view>
				</view>
			</u-col>
		</u-row>
		<u-toast ref="uToast" />
	</view>
</template>

<script>
	import utils from '../../common/utils.js'
	import request from '../../common/api.js'
	import md5 from '../../common/md5.js'

	export default {
		data() {
			return {
				span: 12,
				offset: 0,
				username: '',
				password: '',
				uuid: '',
				token: '',
				showLoading: false
			}
		},
		created() {
			// 获取客户端信息
			var clientWidth
			var platform
			uni.getSystemInfo({
				success: (res) => {
					platform = res.platform
					clientWidth = res.windowWidth
				}
			})
			// 客户端宽大于768小于992改变输入框占比
			if (clientWidth > 768 && clientWidth <= 992) {
				this.span = 10
				this.offset = 1
			}
			// 客户端宽大于992改变输入框占比
			if (clientWidth > 992) {
				this.span = 6
				this.offset = 3
			}

			// 获取本地储存信息
			var username = uni.getStorageSync('username')
			var uuid = uni.getStorageSync('uuid')
			var token = uni.getStorageSync('token')
			// 如果有信息
			if (uuid && token) {
				this.username = username
				this.uuid = uuid
				this.token = token
				// 执行免验证登录
				this.freeLogin(uuid, token)
			}
		},
		methods: {
			// 免验证登录
			freeLogin(uuid, token) {
				// 发起请求
				var freeLogin = request.api.freeLogin
				uni.showLoading({
					title: '加载中...'
				})
				this.$utils.request(freeLogin.url, freeLogin.method, {
					uuid: this.uuid,
					token: this.token
				}).then((res) => {
					// 请求成功
					uni.hideLoading()
					this.$refs.uToast.show({
						title: '登录成功',
						type: 'success',
						duration: 1500
					})
					// 跳转到首页
					setTimeout(() => {
						uni.switchTab({
							url: '../index/index'
						})
					}, 1500)
				}, (reason) => {
					// 请求失败
					uni.hideLoading()
					if (reason.code == 401) {
						uni.removeStorageSync('token')
						this.$refs.uToast.show({
							title: 'token过期，请重新登录',
							type: 'error',
							icon: false,
							duration: 1500
						})
					} else {
						this.$refs.uToast.show({
							title: '未知错误',
							type: 'error',
							duration: 1500
						})
					}
				})
			},
			// 提交登录信息
			login() {
				if (!this.username) return this.$refs.uToast.show({
					title: '请输入用户名',
					type: 'warning',
					duration: 1500
				})
				if (!this.password) return this.$refs.uToast.show({
					title: '请输入密码',
					type: 'warning',
					duration: 1500
				})
				uni.vibrateShort()
				// 发起请求
				var login = request.api.login
				uni.showLoading({
					title: '加载中...'
				})
				this.$utils.request(login.url, login.method, {
					username: this.username,
					password: md5.hex_md5(this.password)
				}).then((res) => {
					// console.log(res)
					// 请求结果：成功
					uni.hideLoading()
					this.$refs.uToast.show({
						title: '登录成功',
						type: 'success',
						duration: 1500
					})
					// 设置本地储存
					uni.setStorageSync('username', res.data.username)
					uni.setStorageSync('uuid', res.data.uuid)
					uni.setStorageSync('token', res.data.token)
					// 跳转到首页
					setTimeout(() => {
						uni.switchTab({
							url: '../index/index'
						})
					}, 1500)
				}, (reason) => {
					// console.log(reason)
					// 请求结果：失败
					// 网络错误
					uni.hideLoading()
					if (reason.code == 500) {
						this.$refs.uToast.show({
							title: '网络错误',
							type: 'error',
							duration: 1500
						})
						// 用户名或密码错误
					} else if (reason.code == 401) {
						this.$refs.uToast.show({
							title: '用户名 或 密码错误',
							type: 'error',
							duration: 1500
						})
						// 用户未注册
					} else if (reason.code == 400) {
						this.$refs.uToast.show({
							title: '此用户名未注册',
							type: 'error',
							duration: 1500
						})
					} else {
						this.$refs.uToast.show({
							title: '未知错误',
							type: 'error',
							duration: 1500
						})
					}
				})
			}
		}
	}
</script>

<style scoped>
	.sun-logo-box {
		display: flex;
		justify-content: center;
		align-items: center;
		/* width: 750rpx;
		height: 300rpx; */
		margin: 0 auto;
		width: 375px;
		height: 150px;
	}

	.sun-logo {
		display: flex;
		justify-content: center;
		align-items: center;
		/* width: 180rpx;
		height: 180rpx; */
		border-radius: 15rpx;
		width: 90px;
		height: 90px;
		/* background-color: #12C8B9; */
		/* box-shadow: 0rpx 0rpx 30rpx rgba(18,200,185,0.5); */
	}

	.sun-logo image {
		/* width: 180rpx;
		height: 180rpx; */
		width: 90px;
		height: 90px;
	}

	.close-icon {
		width: 17px;
		height: 17px;
	}

	.sun-icon-img {
		width: 60px;
		height: 60px;
	}

	.sun-login-box {
		padding: 10px 30px;
	}

	.sun-label {
		display: flex;
		align-items: center;
	}

	.label-text {
		margin-left: 8px;
		font-weight: 500;
		color: #272e2d;
		font-size: 15px;
	}

	.sun-input-box {
		display: flex;
		align-items: center;
		height: 50px;
		border-bottom: 1rpx solid #eee;
		padding: 0px 5px;
	}

	.sun-input-box input {
		flex: 1;
	}

	.placeholder-class {
		font-size: 15px;
		color: #C0C0C0;
	}

	.sun-tip {
		display: flex;
		justify-content: space-between;
		padding: 0rpx 34px;
	}

	.sun-tip-text {
		font-size: 14px;
		color: #2b85e4;
	}

	.login-btn-box {
		margin: 0 auto;
		width: 300px;
		padding: 25px 34px 0px;
	}

	.login-btn {
		height: 41px;
		border-radius: 20.5px;
		background-color: #2b85e4;
		box-shadow: -1px 12px 11px 0px rgba(105, 141, 225, 0.4);
		text-align: center;
		line-height: 41px;
		font-size: 18px;
		font-weight: 500;
		color: #fff;
	}
</style>
