<div>
    <!-- Order your soul. Reduce your wants. - Augustine -->

    <div id="output" style="width: 400px; height: 200px;"></div>
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
    <script>
        console.log("VexFlow Build:", Vex.Flow.BUILD);

        const { Renderer, Stave, StaveNote, Voice, Formatter } = Vex.Flow;

        // Create an SVG renderer and attach it to the DIV element named "output".
        const div = document.getElementById("output");
        const renderer = new Renderer(div, Renderer.Backends.SVG);

        // Configure the rendering context.
        renderer.resize(420, 200);
        const context = renderer.getContext();

        // Create a stave of width 400 at position 10, 40 on the canvas.
        const stave = new Stave(10, 40, 600);

        // Add a clef and time signature.
        stave.addClef("treble");

        stave.setNoteStartX(stave.getNoteStartX() + 40); // Adds padding after clef

        // Connect it to the rendering context and draw!
        stave.setContext(context).draw();

        // Create the notes
        const notesFromParams = "{{ $target }}";
        notesParsed = notesFromParams.split(',');
        console.log(notesParsed);
        duration = notesParsed.length > 1 ? "h" : "1";
        const notes = notesParsed.map(note => new StaveNote({ keys: [note ], duration: duration }));

        // const notes = [
        //     // A quarter-note C.
        //     new StaveNote({ keys: ["c/4"], duration: "q" }),

        //     // A quarter-note D.
        //     new StaveNote({ keys: ["d/4"], duration: "q" }),

        //     // A quarter-note rest. Note that the key (b/4) specifies the vertical
        //     // position of the rest.
        //     new StaveNote({ keys: ["b/4"], duration: "qr" }),

        //     // A C-Major chord.
        //     new StaveNote({ keys: ["c/4", "e/4", "g/4"], duration: "q" }),
        // ];

        // Create a voice in 4/4 and add above notes
        const voice = new Voice({ numBeats: 2, beatValue: 2 });
        voice.addTickables(notes);

        // Format and justify the notes to 400 pixels.
        new Formatter().joinVoices([voice]).format([voice], 300);

        // Render voice
        voice.draw(context, stave);
    </script>
</div>