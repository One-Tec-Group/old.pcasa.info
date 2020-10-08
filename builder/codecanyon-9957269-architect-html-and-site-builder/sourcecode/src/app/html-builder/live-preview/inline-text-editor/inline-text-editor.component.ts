import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {baseFonts, fontWeights} from '../../text-style-values';
import {fontAwesomeIconsList} from '../../font-awesome-icons-list';
import {Settings} from 'common/core/config/settings.service';
import {UndoManager} from '../../undo-manager/undo-manager.service';
import {BuilderDocument} from '../../builder-document.service';
import {OverlayRef} from '@angular/cdk/overlay';
import {DomHelpers} from '../../../shared/dom-helpers.service';
import {SelectedElement} from '../selected-element.service';

@Component({
    selector: 'inline-text-editor',
    templateUrl: './inline-text-editor.component.html',
    styleUrls: ['./inline-text-editor.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class InlineTextEditorComponent implements OnInit, OnDestroy {

    /**
     * Styles for rendering editor toolbar selects.
     */
    public styles = {
        fonts: baseFonts,
        weights: fontWeights,
        sizes: [1, 2, 3, 4, 5, 6, 7],
        icons: fontAwesomeIconsList
    };

    /**
     * Model for link panel input field.
     */
    public linkModel: string;

    /**
     * Whether link panel is currently open.
     */
    public linkPanelIsOpen: boolean;

    /**
     * Whether icons panel is currently open.
     */
    public iconsPanelIsOpen: boolean;

    /**
     * Parent of editable node before any changes were made to it.
     */
    private beforeDomNode: HTMLElement;

    /**
     * Node that is being edited by the inline text editor.
     */
    private editedNode: HTMLElement;

    /**
     * Reference to this editor's overlay.
     */
    private overlayRef: OverlayRef;

    constructor(
        private builderDocument: BuilderDocument,
        private settings: Settings,
        private undoManager: UndoManager,
        private selectedElement: SelectedElement,
    ) {}

    ngOnInit() {
        this.editedNode = this.builderDocument.find('[contenteditable]');
        this.beforeDomNode = this.editedNode.parentElement.cloneNode(true) as HTMLElement;
    }

    ngOnDestroy() {
        this.makeNodesNotEditable();

        this.undoManager.wrapDomChanges(this.editedNode.parentElement, null, {before: this.beforeDomNode});
        this.builderDocument.contentChanged.next('builder');
    }

    /**
     * Execute specified command on current text selection.
     */
    public execCommand(command: string, value?: string) {
        this.builderDocument.execCommand(command, value);
    }

    /**
     * Check if specified command is active on current text selection.
     */
    public commandIsActive(command: string) {
        return this.builderDocument.queryCommandState(command);
    }

    /**
     * Create link from current text selection and link model.
     */
    public createLink() {
        this.execCommand('createLink', this.linkModel);
        this.linkModel = null;
        this.togglePanel('link');
    }

    /**
     * Insert specified icon instead of current text selection.
     */
    public insertIcon(icon: string) {
        this.execCommand('insertHtml', '<i class="' + icon + '"></i>');
        this.togglePanel('icons');
    }

    /**
     * Toggle visibility of specified panel.
     */
    public togglePanel(name: 'icons'|'link') {
        this[name + 'PanelIsOpen'] = !this[name + 'PanelIsOpen'];
        if (name === 'icons') this.loadFontAwesome();
        setTimeout(() => this.overlayRef.updatePosition());
    }

    /**
     * Remove "contenteditable" attribute from all nodes.
     */
    private makeNodesNotEditable() {
        const editable = this.builderDocument.findAll('[contenteditable]');

        for (let i = editable.length - 1; i >= 0; i--) {
            editable[i].removeAttribute('contenteditable');
            editable[i]['blur']();
        }
    }

    /**
     * Load font awesome link into main document, if not already loaded.
     */
    private loadFontAwesome() {
        if (document.head.querySelector('#font-awesome')) return;

        document.head.appendChild(
            DomHelpers.createLink(this.settings.getBaseUrl() + 'builder/css/font-awesome.min.css', 'font-awesome')
        );
    }

    public setOverlayRef(overlayRef: OverlayRef) {
        this.overlayRef = overlayRef;
    }

    public shouldEnableLinkBtn() {
        // prevent adding links <a> to button elements
        return this.selectedElement.node.nodeName.toLowerCase() !== 'button';
    }
}
