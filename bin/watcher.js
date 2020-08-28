const chokidar = require('chokidar');
const WebSocketServer = require('websocket').server;
const http = require('http');

const args = process.argv.slice(2);
if (2 !== args.length) {
	process.exit(1);
}

// WEBSOCKET SERVER
// ==================
const server = http.createServer((request, response) => {
	console.log(new Date() + ' Received request for ' + request.url);
	response.writeHead(404);
	response.end();
});

server.listen(args[0], () => {
	console.log(new Date() + ' Server is listening on port ' + args[0]);
});

const wsServer = new WebSocketServer({
	httpServer: server,
	autoAcceptConnections: false,
});

let clients = [];
wsServer.on('request', (request) => {
	// if origin is not allow
	if (!originIsAllowed(request.origin)) {
		request.reject();
		console.log(
			new Date().toString() +
				'Connection from origin' +
				request.origin +
				'rejected'
		);
		return;
	}
	try {
		let connection = request.accept(
			'reload-bundle-protocol',
			request.origin
		);
		(connection._uid = request.key), clients.push(connection);

		console.log(
			new Date().toString() + ' ' + connection._uid + ' connected.'
		);

		connection.on('close', (reasonCode, description) => {
			clients = clients.filter((c) => connection._uid !== c._uid);
			console.log(
				new Date().toString() + ' ' + connection._uid + ' disconnected.'
			);
		});
	} catch (error) {}
});

// FILES WATCHER
// ==================
chokidar.watch(args[1] + '/**/*.twig').on('change', (filePath, infos) => {
	if (!__filename.includes(filePath)) {
		filePath = _normalize(filePath)
			.replace(_normalize(args[1]), '')
			.replace('/', '');
		clients.forEach((client) => {
			client.sendUTF(filePath);
		});
		console.log('[*] Update detected => ' + filePath);
	}
});

// FUNCTIONS
// ==================
function originIsAllowed(origin) {
	// put logic here to detect whether the specified origin is allowed.
	return true;
}

function _normalize(value) {
	return value.replace(/\\/g, '/');
}
