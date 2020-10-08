import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {LivePreview} from "../../live-preview.service";
import {LayoutPanel} from "../layout-panel/layout-panel.service";
import {Inspector} from "../inspector.service";
import {Modal} from "common/core/ui/dialogs/modal.service";
import {ActiveProject} from "../../projects/active-project";
import {LinkEditor} from "../../live-preview/link-editor/link-editor.service";
import {Elements} from "../../elements/elements.service";
import {SelectedElement} from "../../live-preview/selected-element.service";
import {openUploadWindow} from '../../../../common/uploads/utils/open-upload-window';
import {UploadInputTypes} from '../../../../common/uploads/upload-input-config';
import {UploadQueueService} from '../../../../common/uploads/upload-queue/upload-queue.service';
import {Settings} from '../../../../common/core/config/settings.service';

@Component({
    selector: 'inspector-panel',
    templateUrl: './inspector-panel.component.html',
    styleUrls: ['./inspector-panel.component.scss'],
    providers: [UploadQueueService],
    encapsulation: ViewEncapsulation.None,
})
export class InspectorPanelComponent implements OnInit {
    public path = [];

    constructor(
        public livePreview: LivePreview,
        public selected: SelectedElement,
        private layout: LayoutPanel,
        private inspector: Inspector,
        private modal: Modal,
        private activeProject: ActiveProject,
        private linkEditor: LinkEditor,
        private elements: Elements,
        private uploadQueue: UploadQueueService,
        private settings: Settings,
    ) {}

    ngOnInit() {
        this.selected.changed.subscribe(() => {
            if ( ! this.selected.path) return;
            this.path = this.selected.path.slice();
        });
    }

    /**
     * Check if specified property/style of this element can be modified.
     */
    public canModify(property: string) {
        return this.livePreview.selected.canModify(property);
    }

    /**
     * Open layout panel for currently selected element.
     */
    public openLayoutPanel() {
        this.layout.selectRowAndContainerUsing(this.livePreview.selected.node);
        this.inspector.openPanel('layout');
    }

    public openUploadImageModal() {
        const config = {uri: 'uploads/images', httpParams: {path: this.activeProject.getBaseUrl(true)+'images'}};
        openUploadWindow({types: [UploadInputTypes.image]}).then(files => {
            this.uploadQueue.start(files, config).subscribe(entry => {
                this.livePreview.selected.node['src'] = this.settings.getBaseUrl(true) + entry.url;
            });
        });
    }

    /**
     * Open link editor modal.
     */
    public openLinkEditorModal() {
        this.linkEditor.open(this.livePreview.selected.node as HTMLLinkElement);
    }

    /**
     * Check if currently selected node is column, row or container.
     */
    public selectedIsLayout(): boolean {
        return this.livePreview.selected.isLayout();
    }

    /**
     * Check if currently selected node is an image.
     */
    public selectedIsImage(): boolean {
        return this.livePreview.selected.isImage;
    }

    /**
     * Check if currently selected node is a link.
     */
    public selectedIsLink(): boolean {
        return this.elements.isLink(this.livePreview.selected.node);
    }
}
