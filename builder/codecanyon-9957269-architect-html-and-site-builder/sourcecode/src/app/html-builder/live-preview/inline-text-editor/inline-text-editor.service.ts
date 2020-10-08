import {ComponentRef, ElementRef, Injectable} from '@angular/core';
import {Overlay, OverlayRef} from "@angular/cdk/overlay";
import {InlineTextEditorComponent} from "./inline-text-editor.component";
import {ComponentPortal} from "@angular/cdk/portal";

@Injectable()
export class InlineTextEditor {

    /**
     * Reference to currently open inline text editor overlay.
     */
    public overlayRef: OverlayRef;

    /**
     * InlineTextEditor Constructor.
     */
    constructor(private overlay: Overlay) {}

    /**
     * Open inline text editor and make specified node editable.
     */
    public open(node: HTMLElement) {
        const strategy = this.overlay.position().connectedTo(
            new ElementRef(node),
            {originX: 'center', originY: 'top'},
            {overlayX: 'center', overlayY: 'bottom'}
        ).withFallbackPosition(
            {originX: 'center', originY: 'bottom'},
            {overlayX: 'center', overlayY: 'top'}
        ).withOffsetX(380).withOffsetY(-10);

        if (this.overlayRef) this.overlayRef.dispose();

        this.overlayRef = this.overlay.create({positionStrategy: strategy, hasBackdrop: false});
        const componentRef = this.overlayRef
            .attach(new ComponentPortal(InlineTextEditorComponent)) as ComponentRef<InlineTextEditorComponent>;

        componentRef.instance.setOverlayRef(this.overlayRef);

        node.setAttribute('contenteditable', 'true');
        node.focus();
    }

    /**
     * Close currently open inline text editor.
     */
    public close() {
        this.overlayRef && this.overlayRef.dispose();
    }
}
