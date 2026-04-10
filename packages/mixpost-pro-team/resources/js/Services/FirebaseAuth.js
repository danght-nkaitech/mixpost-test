import { getApps, initializeApp } from 'firebase/app'
import {
    getAuth,
    GoogleAuthProvider,
    signInWithEmailAndPassword,
    signInWithPopup,
} from 'firebase/auth'

let firebaseApp = null
let firebaseAuth = null

export function initFirebase(config) {
  if (!config?.apiKey || !config?.projectId) return false

  if (!getApps().length) {
    firebaseApp = initializeApp(config)
  } else {
    firebaseApp = getApps()[0]
  }

  firebaseAuth = getAuth(firebaseApp)
  return true
}

export function isFirebaseReady() {
  return !!firebaseAuth
}

export async function signInWithGoogle() {
  const provider = new GoogleAuthProvider()
  const result = await signInWithPopup(firebaseAuth, provider)
  return result.user.getIdToken()
}

export async function signInWithEmail(email, password) {
  const result = await signInWithEmailAndPassword(firebaseAuth, email, password)
  return result.user.getIdToken()
}
