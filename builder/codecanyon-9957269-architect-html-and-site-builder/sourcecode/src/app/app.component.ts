import {Component, ElementRef, OnInit, ViewChild, ViewContainerRef, ViewEncapsulation} from '@angular/core';
import {ContextMenu} from "common/core/ui/context-menu/context-menu.service";
import {Settings} from "common/core/config/settings.service";
import {AppHttpClient} from "common/core/http/app-http-client.service";
import {NavigationEnd, Router} from "@angular/router";
import {CustomHomepage} from '../common/core/pages/custom-homepage.service';
import {filter} from 'rxjs/operators';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AppComponent implements OnInit {
    @ViewChild('contextMenuViewRef', {read: ViewContainerRef}) contextMenuViewRef;
    @ViewChild('contextMenuOrigin') contextMenuOrigin: ElementRef;

    constructor(
        private contextMenu: ContextMenu,
        private customHomepage: CustomHomepage,
        private settings: Settings,
        private httpClient: AppHttpClient,
        private router: Router,
    ) {}

    ngOnInit() {
        this.customHomepage.select();
        this.settings.setHttpClient(this.httpClient);

        //google analytics
        if (this.settings.get('analytics.tracking_code')) {
            this.triggerAnalyticsPageView();
        }
    }

    private triggerAnalyticsPageView() {
        this.router.events
            .pipe(filter(e => e instanceof NavigationEnd))
            .subscribe((event: NavigationEnd) => {
                if ( ! window['ga']) return;
                window['ga']('set', 'page', event.urlAfterRedirects);
                window['ga']('send', 'pageview');
            });
    }
}
