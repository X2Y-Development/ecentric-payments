(function (n, t) {
    typeof module != "undefined" ? module.exports = t() : typeof define == "function" && typeof define.amd == "object" ? define(t) : this[n] = t()
})("hpp", function () {
    "use strict";
    function t(n, t) {
        for (var i in t)n.setAttribute(i, t[i])
    }

    function i(n, t) {
        function f(n, t) {
            var i = encodeURIComponent(n);
            return t == void 0 ? null : t instanceof Date ? i + "=" + t.toJSON().replace(/\"/g, "") : i + "=" + encodeURIComponent(t)
        }

        var i, r, u;
        if (t == void 0)return n;
        n = n + "?";
        for (i in t)if (r = t[i], Array.isArray(r))r.forEach(function (t) {
            var r = f(i, t);
            r && (n += r + "&")
        }); else {
            if (u = f(i, r), !u)return;
            n += u + "&"
        }
        return n.replace(/&$/, "")
    }

    function n(n, r, u, f, e, o) {
        var s = this, h;
        this.successFn = r || function () {
            return null
        };
        this.failureFn = u || function () {
            return null
        };
        this.data = n;
        this.target = e;
        this.frameDefined = !1;
        this.type = o;
        h = document.createElement("iframe");
        h.style.width = "100%";
        h.style.height = "100%";
        t(h, {name: "HPP", frameborder: 0, scrolling: "no", src: i(s.target, s.data)});
        f != void 0 ? (s.iFC = f, s.frameDefined = !0) : (document.body.style.overflow = "hidden", s.iFC = document.createElement("div"), s.iFC.style.setProperty("overflow-y", "auto"), s.iFC.style.setProperty("-webkit-overflow-scrolling", "touch", "important"), s.iFC.style.width = "100%", s.iFC.style.zIndex = "1000", s.iFC.style.position = "fixed", s.iFC.style.top = "0", s.iFC.style.left = "0", s.iFC.style.height = "100%", s.iFC.style.right = "0", s.iFC.style.bottom = "0", t(s.iFC, {name: "HPPHolder"}), document.body.appendChild(s.iFC));
        s.iFC.appendChild(h);
        h.contentWindow.focus();
        this.callFn = function (n) {
            s.listener.call(s, n)
        };
        window.addEventListener("message", s.callFn)
    }

    function r(t, i, r, u) {
        return new n(t, i, r, u, "https://sandbox.ecentric.co.za/HPP/Payment/HostedPost", "HPP")
    }

    function u(t, i, r) {
        return new n(t, i, null, r, "https://sandbox.ecentric.co.za/HPP/Wallet", "Wallet")
    }

    function f(t, i, r) {
        return new n(t, i, null, r, "https://sandbox.ecentric.co.za/HPP/Wallet/addCard_lightbox", "AddCard")
    }

    return n.prototype.listener = function (n) {
        var t;
        if (this.target.indexOf(n.origin) != -1)if (this.type == "HPP") {
            if (t = JSON.parse(n.data), t.appLink != void 0)window.location = t.appLink; else if (t.Result != void 0)if (t.Result.toUpperCase() == "SUCCESS" ? this.successFn(t) : this.failureFn(t), window.removeEventListener("message", this.callFn), this.frameDefined)while (this.iFC.firstChild)this.iFC.removeChild(this.iFC.firstChild); else document.body.removeChild(this.iFC), document.body.style.overflow = ""
        } else if (t = JSON.parse(n.data), this.successFn(t), window.removeEventListener("message", this.callFn), this.frameDefined)while (this.iFC.firstChild)this.iFC.removeChild(this.iFC.firstChild); else document.body.removeChild(this.iFC), document.body.style.overflow = ""
    }, {payment: r, wallet: u, addCard: f}
});
/*
 //# sourceMappingURL=lightbox.min.js.map
 */