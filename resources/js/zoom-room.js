/**
 * Embedded Zoom Meeting SDK for the Lesson Room.
 *
 * Component View (not Client View): Client View takes over document.body and
 * would destroy the lesson side panel, whereas Component View renders into a
 * container we size ourselves.
 *
 * No credentials are baked into the page. window.__zoomRoom carries only the
 * signature endpoint and the CSRF token; the signature, SDK key and (for the
 * teacher) the host ZAK are fetched from the server at join time, after it
 * re-checks authorisation and the join window.
 *
 * Loaded only by resources/views/lessons/room.blade.php, so the SDK's weight
 * never reaches the rest of the site.
 */
import ZoomMtgEmbedded from '@zoom/meetingsdk/embedded';

const settings = window.__zoomRoom;

if (settings && settings.signatureUrl) {
    const root = document.getElementById('zoom-root');
    const status = document.getElementById('zoom-status');
    const button = document.getElementById('zoom-join');

    const fail = (message) => {
        if (status) {
            status.textContent = message;
            status.hidden = false;
        }
        if (button) {
            button.disabled = false;
        }
    };

    const join = async () => {
        if (button) {
            button.disabled = true;
        }

        let credentials;

        try {
            const response = await fetch(settings.signatureUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': settings.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                const body = await response.json().catch(() => ({}));
                fail(body.message || settings.strings.error);

                return;
            }

            credentials = await response.json();
        } catch (error) {
            fail(settings.strings.error);

            return;
        }

        try {
            const client = ZoomMtgEmbedded.createClient();

            await client.init({
                zoomAppRoot: root,
                language: settings.language || 'en-US',
                patchJsMedia: true,
                customize: {
                    video: {
                        isResizable: false,
                        viewSizes: {
                            default: { width: root.clientWidth, height: root.clientHeight },
                        },
                    },
                },
            });

            await client.join({
                signature: credentials.signature,
                sdkKey: credentials.sdkKey,
                meetingNumber: credentials.meetingNumber,
                password: credentials.passcode,
                userName: credentials.userName,
                userEmail: credentials.userEmail,
                // Present for the teacher only — this is what starts the
                // meeting on the pooled host account.
                zak: credentials.zak,
            });

            if (status) {
                status.hidden = true;
            }
            if (button) {
                button.hidden = true;
            }
        } catch (error) {
            fail(settings.strings.error);
        }
    };

    if (button) {
        button.addEventListener('click', join);
    }

    if (settings.autoJoin) {
        join();
    }
}
