<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/third-party/social-media-chat/view') ?'active':'' }}"><a
                class="text-capitalize"
                href="{{route('admin.third-party.social-media-chat.view')}}">{{translate('social_media_chat')}}</a></li>
        <li class="{{ Request::is('admin/third-party/social-login/view') ?'active':'' }}"><a
                class="text-capitalize"
                href="{{route('admin.third-party.social-login.view')}}">{{translate('social_media_login')}}</a></li>
        <li class="{{ Request::is('admin/third-party/mail') ?'active':'' }}">
            <a class="text-capitalize" href="{{route('admin.third-party.mail.index')}}">{{translate('mail_config')}}</a>
        </li>
        <li class="{{ Request::is('admin/third-party/sms-module') ?'active':'' }}"><a
                class="text-capitalize"
                href="{{route('admin.third-party.sms-module')}}">{{translate('SMS_config')}}</a></li>
        <li class="{{ Request::is('admin/third-party/recaptcha') ?'active':'' }}"><a
                class="text-capitalize"
                href="{{route('admin.third-party.captcha')}}">{{translate('recaptcha')}}</a></li>
        <li class="{{ Request::is('admin/third-party/map-api') ?'active':'' }}">
            <a class="text-capitalize"
               href="{{route('admin.third-party.map-api')}}">{{translate('google_map_APIs')}}</a>
        </li>
        <li class="{{ Request::is('admin/third-party/storage-connection-settings/index') ?'active':'' }}">
            <a href="{{route('admin.third-party.storage-connection-settings.index')}}"
               class="text-capitalize">{{translate('storage_connection')}}</a>
        </li>
        <li class="{{ Request::is('admin/third-party/firebase-otp-verification/index') ?'active':'' }}">
            <a href="{{route('admin.third-party.firebase-otp-verification.index')}}"
               class="text-capitalize">{{translate('Firebase_Auth')}}</a>
        </li>
    </ul>
</div>
